<#
.SYNOPSIS
    Runner agen otonom v2 (Laravel 13): membuat proyek Laravel BARU lalu menjalankan Claude Code berulang-ulang
    sampai seluruh checklist docs/PROGRESS.md selesai.

.DESCRIPTION
    Folder proyek = folder induk dari folder "agent" (folder hasil ekstrak zip).
    Run pertama: membuat kerangka Laravel 13 (composer), memasang dompdf + paket npm (Tailwind 4, Vite, font, ikon, Leaflet),
    membangun token CSS dari DESIGN.md, menyiapkan .env + database baru, mengambil aplikasi lama sebagai referensi
    baca-saja (legacy/), git init, branch kerja. Butuh PHP >= 8.3, Node >= 20.19 (atau >= 22.12), composer, git, internet.
    Setiap iterasi = satu sesi "claude -p" baru. Ingatan antar sesi = docs/PROGRESS.md + git.
    Setelah tiap iterasi runner memeriksa sendiri: tes (SQLite memori), route:list, migrasi, dan aturan
    konsistensi (agent/konsistensi.json), lint DESIGN.md dan npm run build; lalu membuat commit checkpoint, mendeteksi macet, menunggu saat
    limit pemakaian, dan berhenti saat selesai/anggaran habis. Aplikasi lama dan database lamanya tidak disentuh.

    Berkas ini sengaja ASCII murni (Windows PowerShell 5.1 membaca berkas tanpa BOM sebagai ANSI).

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\agent\run-agent.ps1 -CheckOnly
    powershell -ExecutionPolicy Bypass -File .\agent\run-agent.ps1
#>
[CmdletBinding()]
param(
    [int]$MaxIterations = 60,
    [int]$MaxTurns = 200,
    [double]$BudgetPerIterationUsd = 25,
    [double]$TotalBudgetUsd = 300,
    [int]$IterationTimeoutMinutes = 150,
    [string]$Model = '',
    [string]$Branch = 'agen/v2',
    [int]$MaxFailures = 5,
    [int]$MaxLimitWaits = 12,
    [int]$LimitWaitMinutes = 30,
    [string]$MysqlBinDir = '',
    [string]$PhpBinDir = '',
    [ValidatePattern('^[A-Za-z0-9_]+$')]
    [string]$DbName = 'sigap_ambulans_v2',
    [string]$LegacyDir = '',
    [string]$LegacyRef = '',
    [switch]$SkipDbBackup,
    [switch]$CheckOnly
)

$ErrorActionPreference = 'Continue'

$script:AgentDir = $PSScriptRoot
$script:Root = Split-Path -Parent $PSScriptRoot
$script:Utf8NoBom = New-Object System.Text.UTF8Encoding($false)
$script:RunLog = $null
$script:Php = $null
$script:Git = $null
$script:Claude = $null
$script:Composer = $null
$script:Node = $null
$script:Npm = $null
$script:OnWindows = ($env:OS -eq 'Windows_NT')

# ---------------------------------------------------------------- util dasar

function Write-Log {
    param([string]$Message, [string]$Level = 'INFO')
    $line = '[{0}] [{1}] {2}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Level, $Message
    $color = 'Gray'
    switch ($Level) {
        'OK'    { $color = 'Green' }
        'WARN'  { $color = 'Yellow' }
        'ERROR' { $color = 'Red' }
        'STEP'  { $color = 'Cyan' }
    }
    Write-Host $line -ForegroundColor $color
    if ($script:RunLog) {
        try { [IO.File]::AppendAllText($script:RunLog, $line + [Environment]::NewLine, $script:Utf8NoBom) } catch { }
    }
}

function Join-P {
    param([string]$Base, [string[]]$Parts)
    if (-not $Base) { return '' }
    $r = $Base
    foreach ($x in $Parts) { $r = Join-Path $r $x }
    return $r
}

function Write-TextFile {
    param([string]$Path, [string]$Text)
    [IO.File]::WriteAllText($Path, $Text, $script:Utf8NoBom)
}

function Read-TextFile {
    param([string]$Path)
    if (Test-Path -LiteralPath $Path -PathType Leaf) { return [IO.File]::ReadAllText($Path, $script:Utf8NoBom) }
    return ''
}

function Get-Prop {
    param($Obj, [string]$Name, $Default = $null)
    if ($null -ne $Obj -and $null -ne $Obj.PSObject -and $Obj.PSObject.Properties[$Name]) { return $Obj.$Name }
    return $Default
}

function ConvertTo-QuotedArg {
    param([string]$Arg)
    if ($null -eq $Arg -or $Arg.Length -eq 0) { return '""' }
    if ($Arg -notmatch '[\s"]') { return $Arg }
    $s = [regex]::Replace($Arg, '(\\*)"', '$1$1\"')
    $s = [regex]::Replace($s, '(\\+)$', '$1$1')
    return '"' + $s + '"'
}

function Find-Executable {
    param([string[]]$Names, [string[]]$PreferDirs = @(), [string[]]$FallbackDirs = @())
    $dirs = New-Object System.Collections.ArrayList
    foreach ($d in $PreferDirs) { if ($d) { [void]$dirs.Add($d) } }
    foreach ($d in ($env:PATH -split [string][IO.Path]::PathSeparator)) { if ($d) { [void]$dirs.Add($d.Trim('"')) } }
    foreach ($d in $FallbackDirs) {
        if (-not $d) { continue }
        if ($d.Contains('*')) {
            foreach ($m in @(Get-Item -Path $d -ErrorAction SilentlyContinue)) { [void]$dirs.Add($m.FullName) }
        } else {
            [void]$dirs.Add($d)
        }
    }
    $exts = @('.exe', '.cmd', '.bat')
    if (-not $script:OnWindows) { $exts = @('') }
    foreach ($d in $dirs) {
        foreach ($n in $Names) {
            foreach ($e in $exts) {
                $candidate = Join-Path $d ($n + $e) -ErrorAction SilentlyContinue
                if ($candidate -and (Test-Path -LiteralPath $candidate -PathType Leaf -ErrorAction SilentlyContinue)) { return $candidate }
            }
        }
    }
    return $null
}

function Invoke-Proc {
    param(
        [string]$File,
        [string[]]$ArgList = @(),
        [string]$WorkDir = $script:Root,
        [hashtable]$EnvVars = @{},
        [int]$TimeoutSeconds = 600,
        [int]$HeartbeatSeconds = 0,
        [string]$HeartbeatLabel = ''
    )
    $result = [pscustomobject]@{ ExitCode = -1; Stdout = ''; Stderr = ''; TimedOut = $false; Error = '' }
    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = $File
    $quoted = @()
    foreach ($a in $ArgList) { $quoted += (ConvertTo-QuotedArg $a) }
    $psi.Arguments = ($quoted -join ' ')
    $psi.WorkingDirectory = $WorkDir
    $psi.UseShellExecute = $false
    $psi.CreateNoWindow = $true
    $psi.RedirectStandardInput = $true
    $psi.RedirectStandardOutput = $true
    $psi.RedirectStandardError = $true
    $psi.StandardOutputEncoding = [System.Text.Encoding]::UTF8
    $psi.StandardErrorEncoding = [System.Text.Encoding]::UTF8
    foreach ($k in $EnvVars.Keys) { $psi.EnvironmentVariables[[string]$k] = [string]$EnvVars[$k] }

    $p = New-Object System.Diagnostics.Process
    $p.StartInfo = $psi
    try {
        [void]$p.Start()
    } catch {
        $result.Error = $_.Exception.Message
        return $result
    }
    try { $p.StandardInput.Close() } catch { }
    $outTask = $p.StandardOutput.ReadToEndAsync()
    $errTask = $p.StandardError.ReadToEndAsync()

    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    $nextBeat = $HeartbeatSeconds
    while ($true) {
        if ($p.WaitForExit(10000)) { break }
        if ($sw.Elapsed.TotalSeconds -ge $TimeoutSeconds) {
            $result.TimedOut = $true
            try { & taskkill.exe /PID $p.Id /T /F 2>&1 | Out-Null } catch { }
            try { if (-not $p.HasExited) { $p.Kill() } } catch { }
            break
        }
        if ($HeartbeatSeconds -gt 0 -and $sw.Elapsed.TotalSeconds -ge $nextBeat) {
            Write-Log ('{0}: masih berjalan ({1} menit)...' -f $HeartbeatLabel, [int]$sw.Elapsed.TotalMinutes)
            $nextBeat += $HeartbeatSeconds
        }
    }
    try { [void]$p.WaitForExit(30000) } catch { }
    if ($outTask.Wait(20000)) { $result.Stdout = [string]$outTask.Result }
    if ($errTask.Wait(20000)) { $result.Stderr = [string]$errTask.Result }
    if ($p.HasExited) { $result.ExitCode = $p.ExitCode }
    return $result
}

function Invoke-Git {
    param([string[]]$GitArgs, [int]$TimeoutSeconds = 180)
    return (Invoke-Proc -File $script:Git -ArgList $GitArgs -TimeoutSeconds $TimeoutSeconds)
}

function Set-KeepAwake {
    param([bool]$On)
    if (-not $script:OnWindows) { return }
    try {
        if (-not ('SigapPower' -as [type])) {
            Add-Type -TypeDefinition 'using System.Runtime.InteropServices; public static class SigapPower { [DllImport("kernel32.dll")] public static extern uint SetThreadExecutionState(uint f); }'
        }
        if ($On) { [void][SigapPower]::SetThreadExecutionState([uint32]2147483649) }
        else { [void][SigapPower]::SetThreadExecutionState([uint32]2147483648) }
    } catch { }
}

# ---------------------------------------------------------------- .env, backup, pengaman

function Read-DotEnv {
    param([string]$Path)
    $map = @{}
    foreach ($line in (Get-Content -LiteralPath $Path -Encoding UTF8)) {
        $t = $line.Trim()
        if ($t.Length -eq 0 -or $t.StartsWith('#')) { continue }
        $idx = $t.IndexOf('=')
        if ($idx -lt 1) { continue }
        $k = $t.Substring(0, $idx).Trim()
        $v = $t.Substring($idx + 1).Trim()
        $quoted = ($v.Length -ge 2) -and (($v.StartsWith('"') -and $v.EndsWith('"')) -or ($v.StartsWith("'") -and $v.EndsWith("'")))
        if ($quoted) {
            $v = $v.Substring(1, $v.Length - 2)
        } else {
            $h = $v.IndexOf(' #')
            if ($h -ge 0) { $v = $v.Substring(0, $h).Trim() }
        }
        $map[$k] = $v
    }
    return $map
}

function Backup-Database {
    $envPath = Join-Path $script:Root '.env'
    if (-not (Test-Path -LiteralPath $envPath -PathType Leaf)) {
        Write-Log 'Berkas .env tidak ditemukan di folder proyek.' 'ERROR'
        return $false
    }
    $e = Read-DotEnv $envPath
    $conn = [string]$e['DB_CONNECTION']
    if (-not $conn) { $conn = 'mysql' }
    $stamp = Get-Date -Format 'yyyyMMdd-HHmmss'

    if ($conn -eq 'sqlite') {
        $dbFile = [string]$e['DB_DATABASE']
        if (-not $dbFile) { $dbFile = Join-P $script:Root @('database', 'database.sqlite') }
        elseif (-not [IO.Path]::IsPathRooted($dbFile)) { $dbFile = Join-Path $script:Root $dbFile }
        if (-not (Test-Path -LiteralPath $dbFile -PathType Leaf)) { Write-Log ('File SQLite tidak ditemukan: ' + $dbFile) 'ERROR'; return $false }
        if ($CheckOnly) { Write-Log 'Backup SQLite akan dibuat saat run sungguhan.' 'OK'; return $true }
        $dest = Join-Path $script:BackupDir ('db-' + $stamp + '.sqlite')
        Copy-Item -LiteralPath $dbFile -Destination $dest
        Write-Log ('Backup database dibuat: ' + $dest) 'OK'
        return $true
    }

    if ($conn -ne 'mysql' -and $conn -ne 'mariadb') {
        Write-Log ('DB_CONNECTION=' + $conn + ' tidak didukung untuk backup otomatis. Jalankan ulang dengan -SkipDbBackup bila Anda sudah membackup sendiri.') 'ERROR'
        return $false
    }

    $dump = Find-Executable -Names @('mysqldump') -PreferDirs @($MysqlBinDir) -FallbackDirs (Get-MysqlDirs)
    if (-not $dump) {
        Write-Log 'mysqldump tidak ditemukan. Gunakan -MysqlBinDir "C:\xampp\mysql\bin" (sesuaikan), atau -SkipDbBackup bila Anda sudah membackup database sendiri.' 'ERROR'
        return $false
    }
    $hostName = [string]$e['DB_HOST']; if (-not $hostName) { $hostName = '127.0.0.1' }
    $port = [string]$e['DB_PORT']; if (-not $port) { $port = '3306' }
    $user = [string]$e['DB_USERNAME']; if (-not $user) { $user = 'root' }
    $db = [string]$e['DB_DATABASE']
    $pass = [string]$e['DB_PASSWORD']
    if (-not $db) { Write-Log 'DB_DATABASE kosong di .env.' 'ERROR'; return $false }
    if ($CheckOnly) { Write-Log ('mysqldump ditemukan: ' + $dump + ' (backup akan dibuat saat run sungguhan).') 'OK'; return $true }

    $dest = Join-Path $script:BackupDir ('db-' + $stamp + '.sql')
    $envVars = @{}
    if ($pass) { $envVars['MYSQL_PWD'] = $pass }
    $extras = @()
    $okFile = $false
    $r = $null
    for ($try = 0; $try -lt 3; $try++) {
        # Penting: tiap elemen yang memakai + harus dalam tanda kurung (operator koma lebih kuat dari +).
        $dumpArgs = @(('--host=' + $hostName), ('--port=' + $port), ('--user=' + $user), '--single-transaction', '--routines',
                      '--default-character-set=utf8mb4', ('--result-file=' + $dest)) + $extras + @($db)
        $r = Invoke-Proc -File $dump -ArgList $dumpArgs -EnvVars $envVars -TimeoutSeconds 900
        $okFile = $false
        if ($r.ExitCode -eq 0 -and (Test-Path -LiteralPath $dest -PathType Leaf)) {
            $fi = Get-Item -LiteralPath $dest
            if ($fi.Length -gt 200) {
                $head = Read-TextFile $dest
                if ($head -match 'CREATE TABLE|Dump completed') { $okFile = $true }
            }
        }
        if ($okFile) { break }
        # Pengulangan untuk galat umum mysqldump 8 terhadap server lain / user tanpa hak PROCESS.
        if (($r.Stderr -match 'COLUMN_STATISTICS') -and ($extras -notcontains '--column-statistics=0')) { $extras += '--column-statistics=0'; continue }
        if (($r.Stderr -match 'PROCESS') -and ($extras -notcontains '--no-tablespaces')) { $extras += '--no-tablespaces'; continue }
        break
    }
    if (-not $okFile) {
        Write-Log ('Backup database GAGAL (kode ' + $r.ExitCode + '): ' + $r.Stderr.Trim() + ' ' + $r.Error) 'ERROR'
        Write-Log 'Pastikan MySQL menyala dan data DB_* di .env benar. Run dibatalkan demi keamanan data.' 'ERROR'
        return $false
    }
    Write-Log ('Backup database dibuat: ' + $dest) 'OK'
    return $true
}

function Find-PhpunitConfig {
    foreach ($n in @('phpunit.xml', 'phpunit.xml.dist')) {
        $p = Join-Path $script:Root $n
        if (Test-Path -LiteralPath $p -PathType Leaf) { return $p }
    }
    return $null
}

function Test-PhpunitGuard {
    $file = Find-PhpunitConfig
    if (-not $file) { return $false }
    $text = Read-TextFile $file
    $stripped = [regex]::Replace($text, '<!--.*?-->', '', [System.Text.RegularExpressions.RegexOptions]::Singleline)
    $c = $stripped -match '<env\s+name="DB_CONNECTION"\s+value="sqlite"'
    $d = $stripped -match '<env\s+name="DB_DATABASE"\s+value=":memory:"'
    return ($c -and $d)
}

function Set-PhpunitGuard {
    $file = Find-PhpunitConfig
    if (-not $file) {
        Write-Log 'phpunit.xml tidak ditemukan; tes tidak dapat diisolasi dari database dev.' 'ERROR'
        return $false
    }
    $text = Read-TextFile $file
    $stripped = [regex]::Replace($text, '<!--.*?-->', '', [System.Text.RegularExpressions.RegexOptions]::Singleline)
    $hasConn = $stripped -match '<env\s+name="DB_CONNECTION"\s+value="sqlite"'
    $hasDb = $stripped -match '<env\s+name="DB_DATABASE"\s+value=":memory:"'
    $otherConn = ($stripped -match '<env\s+name="DB_CONNECTION"') -and (-not $hasConn)
    $otherDb = ($stripped -match '<env\s+name="DB_DATABASE"') -and (-not $hasDb)
    if ($otherConn -or $otherDb) {
        Write-Log ('phpunit.xml punya pengaturan DB_* selain sqlite/:memory:. Ubah manual menjadi sqlite + :memory: lalu jalankan ulang: ' + $file) 'ERROR'
        return $false
    }
    if ($hasConn -and $hasDb) { Write-Log 'phpunit.xml sudah memaksa SQLite memori.' 'OK'; return $true }
    if ($CheckOnly) { Write-Log 'phpunit.xml akan dipatch agar tes memakai SQLite memori (saat run sungguhan).' 'WARN'; return $true }

    $nl = "`n"
    if ($text.Contains("`r`n")) { $nl = "`r`n" }
    $insert = ''
    if (-not $hasConn) { $insert += '        <env name="DB_CONNECTION" value="sqlite" force="true"/>' + $nl }
    if (-not $hasDb) { $insert += '        <env name="DB_DATABASE" value=":memory:" force="true"/>' + $nl }
    $idx = $text.LastIndexOf('</php>')
    if ($idx -lt 0) { Write-Log 'phpunit.xml tidak punya blok <php>; patch otomatis tidak bisa dilakukan.' 'ERROR'; return $false }
    $lineStart = $text.LastIndexOf("`n", $idx) + 1
    $new = $text.Substring(0, $lineStart) + $insert + $text.Substring($lineStart)
    Write-TextFile $file $new
    Write-Log ('phpunit.xml dipatch: tes dipaksa memakai SQLite memori (' + $file + ').') 'OK'
    return $true
}

function Set-ClaudeMdImport {
    # Kerangka Laravel 13 membawa CLAUDE.md/AGENTS.md berisi perintah memasang Laravel Boost lewat internet.
    # Itu bertentangan dengan aturan agen, jadi diganti dengan impor CLAUDE.agent.md saja.
    $nl = [Environment]::NewLine
    foreach ($name in @('CLAUDE.md', 'AGENTS.md')) {
        $p = Join-Path $script:Root $name
        $text = Read-TextFile $p
        $foreign = ($text -match 'laravel-boost-guidelines')
        if (-not $foreign -and ($text -match '(?m)^\s*@CLAUDE\.agent\.md\s*$')) { continue }
        if ($CheckOnly) {
            if ($foreign) { Write-Log ($name + ' bawaan kerangka (menyuruh memasang Laravel Boost) akan diganti (saat run sungguhan).') 'WARN' }
            else { Write-Log ($name + ' akan diberi baris @CLAUDE.agent.md (saat run sungguhan).') 'WARN' }
            continue
        }
        if ($foreign) {
            Write-TextFile $p ('# Mode agen otonom (dibuat oleh agent/run-agent.ps1)' + $nl + '@CLAUDE.agent.md' + $nl)
            Write-Log ($name + ' bawaan kerangka diganti: hanya mengimpor CLAUDE.agent.md.') 'OK'
            continue
        }
        $prefix = ''
        if ($text.Length -gt 0 -and -not $text.EndsWith("`n")) { $prefix = $nl }
        $add = $prefix + $nl + '# Mode agen otonom (ditambahkan oleh agent/run-agent.ps1)' + $nl + '@CLAUDE.agent.md' + $nl
        [IO.File]::AppendAllText($p, $add, $script:Utf8NoBom)
        Write-Log ($name + ' diberi baris @CLAUDE.agent.md.') 'OK'
    }
}

function Add-GitExclude {
    $dir = Join-P $script:Root @('.git', 'info')
    $file = Join-Path $dir 'exclude'
    if (-not (Test-Path -LiteralPath $dir)) { [void](New-Item -ItemType Directory -Path $dir -Force) }
    $text = Read-TextFile $file
    $add = ''
    foreach ($l in @('agent/logs/', 'agent/backup/', 'agent/last-gate.txt', 'agent/.lock', 'agent/.bootstrapped', 'agent/.frontend-ready', 'agent/legacy.zip', 'legacy/', '.laravel-tmp/')) {
        if ($text -notmatch ('(?m)^' + [regex]::Escape($l) + '\s*$')) { $add += $l + [Environment]::NewLine }
    }
    if ($add.Length -gt 0) {
        $prefix = ''
        if ($text.Length -gt 0 -and -not $text.EndsWith("`n")) { $prefix = [Environment]::NewLine }
        [IO.File]::AppendAllText($file, $prefix + $add, $script:Utf8NoBom)
    }
}

function Test-SamePath {
    param([string]$A, [string]$B)
    $x = ([IO.Path]::GetFullPath($A)).Replace('/', '\').TrimEnd('\')
    $y = ([IO.Path]::GetFullPath($B)).Replace('/', '\').TrimEnd('\')
    return [string]::Equals($x, $y, [StringComparison]::OrdinalIgnoreCase)
}

function Initialize-GitBranch {
    $t = Invoke-Git @('rev-parse', '--show-toplevel')
    if ($t.ExitCode -ne 0) {
        if ($CheckOnly) { Write-Log 'Folder ini belum repositori git; run sungguhan akan menjalankan git init.' 'WARN'; return $true }
        Write-Log 'Folder belum repositori git; menjalankan git init.' 'WARN'
        $i = Invoke-Git @('init')
        if ($i.ExitCode -ne 0) { Write-Log ('git init gagal: ' + $i.Stderr.Trim()) 'ERROR'; return $false }
    } else {
        $top = $t.Stdout.Trim()
        if (-not (Test-SamePath $top $script:Root)) {
            Write-Log ('Folder proyek berada di dalam repositori git lain (' + $top + '). Demi keamanan, jalankan agen hanya pada repositori yang akar-nya adalah folder proyek.') 'ERROR'
            return $false
        }
    }

    $email = (Invoke-Git @('config', 'user.email')).Stdout.Trim()
    $name = (Invoke-Git @('config', 'user.name')).Stdout.Trim()
    if (-not $CheckOnly) {
        if (-not $email) { [void](Invoke-Git @('config', 'user.email', 'agent@localhost')); Write-Log 'git user.email lokal diatur: agent@localhost' 'WARN' }
        if (-not $name) { [void](Invoke-Git @('config', 'user.name', 'SIGAP Agent')); Write-Log 'git user.name lokal diatur: SIGAP Agent' 'WARN' }
        Add-GitExclude
    }

    $hasHead = ((Invoke-Git @('rev-parse', '--verify', '--quiet', 'HEAD')).ExitCode -eq 0)
    if (-not $hasHead) {
        if ($CheckOnly) { return $true }
        [void](Invoke-Git @('add', '-A'))
        $c = Invoke-Git @('commit', '-m', 'chore: kerangka Laravel 13 + paket agen otonom')
        if ($c.ExitCode -ne 0) { Write-Log ('Commit awal gagal: ' + $c.Stderr.Trim()) 'ERROR'; return $false }
    }

    $cur = (Invoke-Git @('rev-parse', '--abbrev-ref', 'HEAD')).Stdout.Trim()
    if ($cur -ne $Branch) {
        $exists = ((Invoke-Git @('rev-parse', '--verify', '--quiet', ('refs/heads/' + $Branch))).ExitCode -eq 0)
        if ($CheckOnly) {
            if ($exists) { Write-Log ('Akan pindah ke branch ' + $Branch + ' (sekarang: ' + $cur + ').') 'WARN' }
            else { Write-Log ('Akan membuat branch ' + $Branch + ' dari ' + $cur + '.') 'WARN' }
            return $true
        }
        if ($exists) { $co = Invoke-Git @('checkout', $Branch) } else { $co = Invoke-Git @('checkout', '-b', $Branch) }
        if ($co.ExitCode -ne 0) {
            Write-Log ('Gagal pindah/membuat branch ' + $Branch + ': ' + $co.Stderr.Trim()) 'ERROR'
            return $false
        }
        Write-Log ('Branch aktif: ' + $Branch) 'OK'
    } else {
        Write-Log ('Branch aktif: ' + $Branch) 'OK'
    }

    if (-not $CheckOnly) {
        $h = Save-Checkpoint 'chore: titik awal sebelum agen otonom (perubahan belum ter-commit + berkas agen)'
        if ($h) { Write-Log ('Perubahan yang belum ter-commit disimpan sebagai titik awal: ' + $h) 'OK' }
    }
    return $true
}

function Save-Checkpoint {
    param([string]$Label)
    $st = Invoke-Git @('status', '--porcelain')
    if ($st.Stdout.Trim().Length -eq 0) { return '' }
    [void](Invoke-Git @('add', '-A'))
    $c = Invoke-Git @('commit', '-m', $Label)
    if ($c.ExitCode -ne 0) {
        Write-Log ('Commit checkpoint gagal: ' + $c.Stderr.Trim() + $c.Stdout.Trim()) 'WARN'
        return ''
    }
    return (Invoke-Git @('rev-parse', '--short', 'HEAD')).Stdout.Trim()
}

# ---------------------------------------------------------------- progres & gate

function Get-Progress {
    $path = Join-P $script:Root @('docs', 'PROGRESS.md')
    $text = Read-TextFile $path
    $o = [pscustomobject]@{ Exists = ($text.Length -gt 0); Status = 'BERJALAN'; Reason = ''; Unchecked = 0; Checked = 0; Blocked = 0; Hash = ''; Path = $path }
    if (-not $o.Exists) { return $o }
    $first = ($text -split "`r?`n")[0]
    if ($first -match '^STATUS_AGEN:\s*(.*)$') {
        $val = $Matches[1].Trim()
        if ($val -match '^(?i)SELESAI') { $o.Status = 'SELESAI' }
        elseif ($val -match '^(?i)BLOKIR') { $o.Status = 'BLOKIR'; $o.Reason = $val }
    }
    $o.Unchecked = ([regex]::Matches($text, '(?m)^\s*- \[ \]')).Count
    $o.Checked = ([regex]::Matches($text, '(?mi)^\s*- \[x\]')).Count
    $o.Blocked = ([regex]::Matches($text, '(?m)^\s*- \[!\]')).Count
    $sha = [System.Security.Cryptography.SHA1]::Create()
    $o.Hash = [BitConverter]::ToString($sha.ComputeHash($script:Utf8NoBom.GetBytes($text))).Replace('-', '')
    return $o
}

function Set-ProgressStatusBerjalan {
    $path = Join-P $script:Root @('docs', 'PROGRESS.md')
    $text = Read-TextFile $path
    if ($text -match '^STATUS_AGEN:\s*SELESAI') {
        $new = [regex]::Replace($text, '^STATUS_AGEN:[^\r\n]*', 'STATUS_AGEN: BERJALAN')
        Write-TextFile $path $new
    }
}

function Get-Fingerprint {
    $h = (Invoke-Git @('rev-parse', 'HEAD')).Stdout.Trim()
    $pr = Get-Progress
    return ($h + '|' + $pr.Hash)
}

function Get-BuildTail {
    # Ujung keluaran npm/vite tanpa baris jejak tumpukan ("    at ..."), supaya galat aslinya terbaca.
    param([string]$Text, [int]$Lines = 25)
    $keep = @()
    foreach ($l in ($Text -split "`r?`n")) { if ($l -notmatch '^\s+at ' -and $l.Trim().Length -gt 0) { $keep += $l } }
    return (Get-TailText ($keep -join [Environment]::NewLine) $Lines)
}

function Get-TailText {
    param([string]$Text, [int]$Lines = 40)
    $arr = $Text -split "`r?`n"
    if ($arr.Count -le $Lines) { return ($arr -join [Environment]::NewLine) }
    return (($arr[($arr.Count - $Lines)..($arr.Count - 1)]) -join [Environment]::NewLine)
}

function Invoke-Gates {
    param([int]$IterNo)
    $nl = [Environment]::NewLine
    $ok = $true
    $sb = New-Object System.Text.StringBuilder
    $logPrefix = Join-Path $script:LogDir ('gate-' + $IterNo.ToString('000'))

    if (-not (Test-PhpunitGuard)) {
        $ok = $false
        [void]$sb.AppendLine('GAGAL: phpunit.xml tidak lagi memaksa SQLite :memory: untuk DB_CONNECTION/DB_DATABASE. Tes TIDAK dijalankan demi keamanan DB dev. (File ini dikunci; runner akan memperbaikinya pada start berikutnya.)')
    } else {
        $phpunit = Join-P $script:Root @('vendor', 'bin', 'phpunit')
        if (-not (Test-Path -LiteralPath $phpunit -PathType Leaf)) {
            $ok = $false
            [void]$sb.AppendLine('GAGAL: vendor/bin/phpunit tidak ada (phpunit belum terpasang). Pasang lewat composer require --dev phpunit/phpunit hanya jika benar-benar hilang; biasanya composer install cukup.')
        } else {
            $envT = @{ DB_CONNECTION = 'sqlite'; DB_DATABASE = ':memory:'; APP_ENV = 'testing' }
            $t = Invoke-Proc -File $script:Php -ArgList @($phpunit) -EnvVars $envT -TimeoutSeconds 1800
            $all = $t.Stdout + $nl + $t.Stderr
            Write-TextFile ($logPrefix + '-tes.txt') $all
            if ($t.TimedOut) {
                $ok = $false
                [void]$sb.AppendLine('GAGAL: tes melewati batas waktu 30 menit (ada tes yang menggantung?).')
            } elseif ($t.ExitCode -ne 0) {
                $ok = $false
                [void]$sb.AppendLine('GAGAL: php vendor/bin/phpunit keluar dengan kode ' + $t.ExitCode + '. Ujung keluaran:')
                [void]$sb.AppendLine((Get-TailText $all 60))
            } else {
                $summary = ''
                foreach ($line in ($all -split "`r?`n")) { if ($line -match '^(OK \(|Tests:)') { $summary = $line.Trim() } }
                [void]$sb.AppendLine('LULUS: tes. ' + $summary)
            }
        }
    }

    $kon = Test-Konsistensi
    if (-not $kon.Ok) { $ok = $false }
    [void]$sb.AppendLine($kon.Text)

    $fe = Test-Frontend
    if (-not $fe.Ok) { $ok = $false }
    [void]$sb.AppendLine($fe.Text)

    $rl = Invoke-Proc -File $script:Php -ArgList @('artisan', 'route:list') -TimeoutSeconds 300
    if ($rl.ExitCode -ne 0) {
        $ok = $false
        [void]$sb.AppendLine('GAGAL: php artisan route:list (aplikasi tidak bisa boot / route putus). Ujung keluaran:')
        [void]$sb.AppendLine((Get-TailText ($rl.Stdout + $nl + $rl.Stderr) 30))
    } else {
        [void]$sb.AppendLine('LULUS: php artisan route:list')
    }

    $ms = Invoke-Proc -File $script:Php -ArgList @('artisan', 'migrate:status') -TimeoutSeconds 300
    if ($ms.ExitCode -ne 0) {
        [void]$sb.AppendLine('CATATAN: migrate:status tidak bisa dibaca (MySQL mati atau koneksi salah?): ' + (Get-TailText ($ms.Stdout + $nl + $ms.Stderr) 8))
    } else {
        $pending = ([regex]::Matches($ms.Stdout, '(?m)\bPending\b')).Count
        if ($pending -gt 0) {
            $ok = $false
            [void]$sb.AppendLine('GAGAL: ada ' + $pending + ' migrasi tertunda di DB v2. Jalankan php artisan migrate:fresh --seed (setelah tes SQLite hijau).')
        } else {
            [void]$sb.AppendLine('LULUS: tidak ada migrasi tertunda di DB v2.')
        }
    }

    $verdict = 'GAGAL'
    if ($ok) { $verdict = 'LULUS' }
    return [pscustomobject]@{ Ok = $ok; Verdict = $verdict; Text = $sb.ToString() }
}

function Write-LastGate {
    param([int]$IterNo, $Gate, [string]$Extra = '')
    $nl = [Environment]::NewLine
    $text = 'HASIL PEMERIKSAAN RUNNER: ' + $Gate.Verdict + $nl +
            'Setelah iterasi ' + $IterNo + ' - ' + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + $nl + $nl + $Gate.Text
    if ($Extra) { $text += $nl + $Extra + $nl }
    Write-TextFile (Join-Path $script:AgentDir 'last-gate.txt') $text
}

# ---------------------------------------------------------------- klasifikasi galat claude

function Test-LimitText {
    param([string]$T)
    return ($T -match '(?i)usage limit|rate limit|limit reached|too many requests|overloaded|\b429\b|\b529\b|try again later|resets? (at|in)')
}

function Test-AuthText {
    param([string]$T)
    return ($T -match '(?i)not logged in|please run /login|invalid api key|authentication_error|oauth token has expired|\b401\b')
}

function Wait-Minutes {
    param([int]$Minutes, [string]$Why)
    Write-Log ('Menunggu {0} menit: {1}' -f $Minutes, $Why) 'WARN'
    $end = (Get-Date).AddMinutes($Minutes)
    while ((Get-Date) -lt $end) { Start-Sleep -Seconds 30 }
}


# ---------------------------------------------------------------- versi PHP / Node

function Get-PhpVersion {
    param([string]$PhpExe)
    $r = Invoke-Proc -File $PhpExe -ArgList @('-r', 'echo PHP_VERSION;') -TimeoutSeconds 60
    if ($r.ExitCode -ne 0) { return $null }
    $m = [regex]::Match($r.Stdout.Trim(), '^(\d+)\.(\d+)\.(\d+)')
    if (-not $m.Success) { return $null }
    return (New-Object System.Version ([int]$m.Groups[1].Value), ([int]$m.Groups[2].Value), ([int]$m.Groups[3].Value))
}

function Add-ToPath {
    param([string]$Dir)
    if (-not $Dir) { return }
    $sep = [string][IO.Path]::PathSeparator
    $parts = @($env:PATH -split $sep)
    if ($parts.Count -gt 0 -and [string]::Equals($parts[0].TrimEnd('\', '/'), $Dir.TrimEnd('\', '/'), [StringComparison]::OrdinalIgnoreCase)) { return }
    $env:PATH = $Dir + $sep + $env:PATH
}

function Select-Php {
    # Laravel 13 butuh PHP >= 8.3. Pilih php pertama yang memenuhi: -PhpBinDir, PATH, lalu folder Laragon/XAMPP/WAMP.
    $min = New-Object System.Version 8, 3, 0
    $cands = New-Object System.Collections.ArrayList
    if ($PhpBinDir) { $c = Find-Executable -Names @('php') -PreferDirs @($PhpBinDir); if ($c) { [void]$cands.Add($c) } }
    $c = Find-Executable -Names @('php'); if ($c) { [void]$cands.Add($c) }
    $fallback = @('C:\laragon\bin\php\*', 'C:\xampp\php', 'C:\wamp64\bin\php\*', 'C:\Program Files\PHP*')
    foreach ($f in $fallback) {
        $dirs = @()
        if ($f.Contains('*')) { $dirs = @(Get-Item -Path $f -ErrorAction SilentlyContinue | Sort-Object -Property Name -Descending | ForEach-Object { $_.FullName }) }
        else { $dirs = @($f) }
        foreach ($d in $dirs) {
            $c = Find-Executable -Names @('php') -PreferDirs @($d)
            if ($c -and $c.StartsWith($d, [StringComparison]::OrdinalIgnoreCase)) { [void]$cands.Add($c) }
        }
    }
    $seen = @{}
    $found = @()
    foreach ($c in $cands) {
        $key = $c.ToLowerInvariant()
        if ($seen.ContainsKey($key)) { continue }
        $seen[$key] = $true
        $v = Get-PhpVersion $c
        if ($null -eq $v) { continue }
        $found += ($c + ' (' + $v.ToString() + ')')
        if ($v -ge $min) {
            Add-ToPath (Split-Path -Parent $c)
            Write-Log ('php: ' + $c + ' (versi ' + $v.ToString() + ')') 'OK'
            return $c
        }
    }
    if ($found.Count -gt 0) { Write-Log ('PHP yang ditemukan terlalu lama: ' + ($found -join '; ')) 'ERROR' }
    else { Write-Log 'php tidak ditemukan.' 'ERROR' }
    Write-Log 'Laravel 13 membutuhkan PHP 8.3 atau lebih baru. Laragon: klik kanan ikon Laragon > PHP > Version > pilih 8.3+ (atau unduh PHP 8.3+ ke C:\laragon\bin\php), atau beri -PhpBinDir "<folder php.exe>".' 'ERROR'
    return $null
}

function Test-NodeOk {
    # Vite 8: Node ^20.19 atau >= 22.12
    $r = Invoke-Proc -File $script:Node -ArgList @('-v') -TimeoutSeconds 60
    $m = [regex]::Match($r.Stdout.Trim(), '^v(\d+)\.(\d+)\.(\d+)')
    if (-not $m.Success) { Write-Log 'Versi node tidak terbaca.' 'ERROR'; return $false }
    $v = New-Object System.Version ([int]$m.Groups[1].Value), ([int]$m.Groups[2].Value), ([int]$m.Groups[3].Value)
    $ok = ($v -ge (New-Object System.Version 22, 12, 0)) -or (($v.Major -eq 20) -and ($v -ge (New-Object System.Version 20, 19, 0)))
    if ($ok) { Write-Log ('node: ' + $script:Node + ' (versi ' + $v.ToString() + ')') 'OK'; return $true }
    Write-Log ('Node ' + $v.ToString() + ' terlalu lama. Dibutuhkan Node 20.19+ atau 22.12+ (LTS terbaru dari nodejs.org; Laragon: menu Node.js).') 'ERROR'
    return $false
}

function Test-NpmReady {
    foreach ($d in @('vite', 'tailwindcss', 'laravel-vite-plugin', '@tailwindcss/vite', '@google/design.md', '@fontsource-variable/roboto', 'lucide-static', 'yaml', 'leaflet', 'leaflet-routing-machine')) {
        if (-not (Test-Path -LiteralPath (Join-P $script:Root @('node_modules', $d)) -PathType Container)) { return $false }
    }
    return $true
}

function Update-DesignTokens {
    # Menghasilkan resources/css/design-tokens.css dari DESIGN.md. Mengembalikan objek Ok/Text.
    $r = Invoke-Proc -File $script:Node -ArgList @('agent/tokens.mjs') -TimeoutSeconds 120
    $txt = ($r.Stdout + ' ' + $r.Stderr + ' ' + $r.Error).Trim()
    return [pscustomobject]@{ Ok = ($r.ExitCode -eq 0); Text = $txt }
}

function Test-DesignLint {
    $cli = Join-P $script:Root @('node_modules', '@google', 'design.md', 'dist', 'index.js')
    $r = Invoke-Proc -File $script:Node -ArgList @($cli, 'lint', 'DESIGN.md') -TimeoutSeconds 120
    $res = [pscustomobject]@{ Ok = $false; Text = '' }
    $j = $null
    try { $j = $r.Stdout | ConvertFrom-Json } catch { $j = $null }
    if ($null -eq $j) {
        $res.Text = 'GAGAL: designmd lint tidak menghasilkan JSON: ' + (Get-TailText ($r.Stdout + [Environment]::NewLine + $r.Stderr + $r.Error) 10)
        return $res
    }
    $errs = [int](Get-Prop (Get-Prop $j 'summary' $null) 'errors' 0)
    $warns = [int](Get-Prop (Get-Prop $j 'summary' $null) 'warnings' 0)
    if ($errs -gt 0) {
        $lines = @()
        foreach ($f in @($j.findings)) { if ((Get-Prop $f 'severity' '') -eq 'error') { $lines += ('  ' + (Get-Prop $f 'rule' '') + ' ' + (Get-Prop $f 'path' '') + ': ' + (Get-Prop $f 'message' '')) } }
        $res.Text = 'GAGAL: DESIGN.md tidak lolos designmd lint (' + $errs + ' galat). Berkas ini dikunci untuk agen; pulihkan dari paket asli (git checkout DESIGN.md oleh pemilik proyek):' + [Environment]::NewLine + ($lines -join [Environment]::NewLine)
        return $res
    }
    $res.Ok = $true
    $res.Text = 'LULUS: designmd lint DESIGN.md (0 galat, ' + $warns + ' peringatan).'
    return $res
}

function Test-Frontend {
    # Dijalankan di gate: token dari DESIGN.md, lint, lalu npm run build.
    $nl = [Environment]::NewLine
    $res = [pscustomobject]@{ Ok = $true; Text = '' }
    $sb = New-Object System.Text.StringBuilder
    if (-not (Test-NpmReady)) {
        $res.Ok = $false
        $res.Text = 'GAGAL: node_modules tidak lengkap. Jalankan ulang skrip runner (ia memasang paket npm); agen tidak boleh npm install.'
        return $res
    }
    $lint = Test-DesignLint
    if (-not $lint.Ok) { $res.Ok = $false }
    [void]$sb.AppendLine($lint.Text)
    $tok = Update-DesignTokens
    if (-not $tok.Ok) { $res.Ok = $false; [void]$sb.AppendLine('GAGAL: agent/tokens.mjs: ' + $tok.Text) }
    else { [void]$sb.AppendLine('LULUS: design-tokens.css dihasilkan dari DESIGN.md.') }
    $b = Invoke-Proc -File $script:Npm -ArgList @('run', 'build') -EnvVars @{ NO_COLOR = '1'; FORCE_COLOR = '0' } -TimeoutSeconds 900
    if ($b.ExitCode -ne 0) {
        $res.Ok = $false
        [void]$sb.AppendLine('GAGAL: npm run build (CSS/JS tidak bisa dikompilasi). Ujung keluaran:')
        [void]$sb.AppendLine((Get-BuildTail ($b.Stdout + $nl + $b.Stderr + $b.Error) 25))
    } else {
        [void]$sb.AppendLine('LULUS: npm run build')
    }
    $res.Text = $sb.ToString().TrimEnd()
    return $res
}

# ---------------------------------------------------------------- bootstrap proyek baru, legacy, konsistensi

function Get-MysqlDirs {
    return @('C:\xampp\mysql\bin', 'C:\laragon\bin\mysql\*\bin', 'C:\wamp64\bin\mysql\*\bin', 'C:\wamp\bin\mysql\*\bin',
             'C:\Program Files\MySQL\MySQL Server *\bin', 'C:\Program Files\MariaDB *\bin')
}

function Format-EnvValue {
    param([string]$Value)
    if ($Value -match '^[A-Za-z0-9_.:/\\-]*$') { return $Value }
    if ($Value.IndexOf("'") -lt 0) { return ("'" + $Value + "'") }
    return ('"' + ($Value.Replace('\', '\\').Replace('"', '\"')) + '"')
}

function Set-DotEnvValue {
    param([string]$Path, [string]$Key, [string]$Value)
    $text = Read-TextFile $Path
    $line = $Key + '=' + (Format-EnvValue $Value)
    $pattern = '(?m)^' + [regex]::Escape($Key) + '=.*$'
    if ([regex]::IsMatch($text, $pattern)) {
        $ev = [System.Text.RegularExpressions.MatchEvaluator]{ param($m) $line }
        $text = [regex]::Replace($text, $pattern, $ev)
    } else {
        if ($text.Length -gt 0 -and -not $text.EndsWith("`n")) { $text += [Environment]::NewLine }
        $text += $line + [Environment]::NewLine
    }
    Write-TextFile $Path $text
}

function Invoke-GitIn {
    param([string]$Dir, [string[]]$GitArgs, [int]$TimeoutSeconds = 300)
    return (Invoke-Proc -File $script:Git -ArgList $GitArgs -WorkDir $Dir -TimeoutSeconds $TimeoutSeconds)
}

function Find-LegacyDir {
    $cands = @()
    if ($LegacyDir) { $cands += $LegacyDir }
    $p1 = Split-Path -Parent $script:Root
    if ($p1) {
        $cands += (Join-Path $p1 'ambulance-system')
        $p2 = Split-Path -Parent $p1
        if ($p2) { $cands += (Join-Path $p2 'ambulance-system') }
    }
    foreach ($c in $cands) {
        if ($c -and (Test-Path -LiteralPath (Join-Path $c 'artisan') -PathType Leaf)) { return $c }
    }
    return $null
}

function Test-LegacyProjectFolder {
    # Mencegah agen v2 berjalan di dalam folder proyek LAMA.
    if (Test-Path -LiteralPath (Join-P $script:Root @('app', 'Models', 'RumahSakit.php')) -PathType Leaf) { return $true }
    $mig = Join-P $script:Root @('database', 'migrations')
    if (Test-Path -LiteralPath $mig -PathType Container) {
        if (@(Get-ChildItem -LiteralPath $mig -Filter '*rumah_sakit*' -ErrorAction SilentlyContinue).Count -gt 0) { return $true }
    }
    return $false
}

function Import-Legacy {
    $dest = Join-Path $script:Root 'legacy'
    if (Test-Path -LiteralPath $dest) { Write-Log 'Referensi legacy/ sudah ada.' 'OK'; return }
    $ld = Find-LegacyDir
    if (-not $ld) {
        Write-Log 'Proyek lama tidak ditemukan (opsional). Agen tetap bisa bekerja dari spesifikasi. Beri -LegacyDir "<folder>" bila ingin referensi.' 'WARN'
        return
    }
    $isRepo = ((Invoke-GitIn $ld @('rev-parse', '--is-inside-work-tree')).ExitCode -eq 0)
    if (-not $isRepo) { Write-Log ('Proyek lama (' + $ld + ') bukan repositori git; referensi dilewati.') 'WARN'; return }
    $ref = ''
    foreach ($c in @($LegacyRef, 'fitur/alur-dinkes', 'audit-aplikasi', 'main', 'master')) {
        if (-not $c) { continue }
        if ((Invoke-GitIn $ld @('rev-parse', '--verify', '--quiet', ('refs/heads/' + $c))).ExitCode -eq 0) { $ref = $c; break }
    }
    if (-not $ref) { $ref = 'HEAD' }
    $zip = Join-Path $script:AgentDir 'legacy.zip'
    if (Test-Path -LiteralPath $zip) { Remove-Item -LiteralPath $zip -Force }
    $a = Invoke-GitIn $ld @('archive', '--format=zip', ('--output=' + $zip), $ref)
    if ($a.ExitCode -ne 0 -or -not (Test-Path -LiteralPath $zip)) {
        Write-Log ('git archive proyek lama gagal: ' + $a.Stderr.Trim() + ' (referensi dilewati)') 'WARN'
        return
    }
    try {
        Expand-Archive -LiteralPath $zip -DestinationPath $dest -Force
        Write-Log ('Referensi aplikasi lama (baca saja) dari ' + $ld + ' @ ' + $ref + ' -> legacy/') 'OK'
    } catch {
        Write-Log ('Gagal mengekstrak referensi lama: ' + $_.Exception.Message + ' (dilewati)') 'WARN'
    }
    if (Test-Path -LiteralPath $zip) { Remove-Item -LiteralPath $zip -Force -ErrorAction SilentlyContinue }
}

function Initialize-Project {
    # Mengembalikan $true bila proyek siap. Tiap langkah idempoten; penanda: agent/.frontend-ready dan agent/.bootstrapped
    $artisan = Join-Path $script:Root 'artisan'
    $marker = Join-P $script:Root @('agent', '.bootstrapped')
    $frontMarker = Join-P $script:Root @('agent', '.frontend-ready')
    $tmp = Join-Path $script:Root '.laravel-tmp'
    $needSkeleton = -not (Test-Path -LiteralPath $artisan -PathType Leaf)
    $needDompdf = -not (Test-Path -LiteralPath (Join-P $script:Root @('vendor', 'barryvdh', 'laravel-dompdf')) -PathType Container)
    $needNpm = -not (Test-NpmReady)
    $needFront = -not (Test-Path -LiteralPath $frontMarker -PathType Leaf)
    $needEnv = -not (Test-Path -LiteralPath $marker -PathType Leaf)

    if (-not $needSkeleton -and -not $needDompdf -and -not $needNpm -and -not $needFront -and -not $needEnv) { return $true }

    if ($CheckOnly) {
        if ($needSkeleton) { Write-Log 'Akan membuat kerangka Laravel 13 (composer create-project) di folder ini.' 'WARN' }
        if ($needDompdf) { Write-Log 'Akan memasang barryvdh/laravel-dompdf.' 'WARN' }
        if ($needNpm) { Write-Log 'Akan memasang paket npm (Tailwind 4, Vite, font Roboto, ikon Lucide, Leaflet, linter DESIGN.md).' 'WARN' }
        if ($needFront) { Write-Log 'Akan menulis app.css/vite.config.js, menyalin ikon, membangun token dari DESIGN.md, dan menjalankan npm run build.' 'WARN' }
        if ($needEnv) { Write-Log ('Akan mengatur .env, membuat database baru "' + $DbName + '", dan menjalankan migrate (database baru).') 'WARN' }
        $m = Find-Executable -Names @('mysql') -PreferDirs @($MysqlBinDir) -FallbackDirs (Get-MysqlDirs)
        if ($m) { Write-Log ('mysql (klien): ' + $m) 'OK' } else { Write-Log 'mysql klien tidak ditemukan; beri -MysqlBinDir "C:\laragon\bin\mysql\<versi>\bin".' 'ERROR'; return $false }
        $ld = Find-LegacyDir
        if ($ld) { Write-Log ('Proyek lama ditemukan (dipakai sebagai referensi baca-saja): ' + $ld) 'OK' }
        else { Write-Log 'Proyek lama tidak ditemukan (opsional).' 'WARN' }
        return $true
    }

    $cenv = @{ COMPOSER_NO_INTERACTION = '1'; COMPOSER_MEMORY_LIMIT = '-1'; COMPOSER_ALLOW_SUPERUSER = '1' }
    $nenv = @{ NO_COLOR = '1'; FORCE_COLOR = '0' }

    if ($needSkeleton) {
        if (-not $script:Composer) { Write-Log 'composer tidak ditemukan di PATH; dibutuhkan untuk membuat kerangka Laravel.' 'ERROR'; return $false }
        if ((Split-Path -Leaf $tmp) -eq '.laravel-tmp' -and (Test-Path -LiteralPath $tmp)) { Remove-Item -LiteralPath $tmp -Recurse -Force }
        Write-Log 'Membuat kerangka Laravel 13 (composer create-project) - beberapa menit, butuh internet...' 'STEP'
        $r = Invoke-Proc -File $script:Composer -ArgList @('create-project', 'laravel/laravel:^13.0', '.laravel-tmp', '--no-interaction', '--prefer-dist') -EnvVars $cenv -TimeoutSeconds 1800 -HeartbeatSeconds 60 -HeartbeatLabel 'composer'
        if ($r.ExitCode -ne 0 -or -not (Test-Path -LiteralPath (Join-Path $tmp 'artisan') -PathType Leaf)) {
            Write-Log ('composer create-project gagal (kode ' + $r.ExitCode + '): ' + (Get-TailText ($r.Stdout + [Environment]::NewLine + $r.Stderr + $r.Error) 15)) 'ERROR'
            return $false
        }
        # Periksa tabrakan nama DULU, baru pindahkan (supaya tidak ada pemindahan setengah jalan).
        $collide = @()
        foreach ($item in @(Get-ChildItem -LiteralPath $tmp -Force)) {
            if (Test-Path -LiteralPath (Join-Path $script:Root $item.Name)) { $collide += $item.Name }
        }
        if ($collide.Count -gt 0) {
            Write-Log ('Tidak bisa memindahkan kerangka karena sudah ada di folder ini: ' + ($collide -join ', ') + '. Pakai folder kosong (hanya isi paket agen) untuk proyek baru.') 'ERROR'
            if ((Split-Path -Leaf $tmp) -eq '.laravel-tmp') { Remove-Item -LiteralPath $tmp -Recurse -Force -ErrorAction SilentlyContinue }
            return $false
        }
        foreach ($item in @(Get-ChildItem -LiteralPath $tmp -Force)) {
            Move-Item -LiteralPath $item.FullName -Destination (Join-Path $script:Root $item.Name)
        }
        if ((Split-Path -Leaf $tmp) -eq '.laravel-tmp') { Remove-Item -LiteralPath $tmp -Recurse -Force -ErrorAction SilentlyContinue }
        # Halaman contoh bawaan Laravel melanggar aturan konsistensi (gaya mentah). Diganti satu baris teks
        # supaya route "/" dan tes bawaan tetap lulus sampai agen membuat landing sungguhan (T3.2).
        $welcome = Join-P $script:Root @('resources', 'views', 'welcome.blade.php')
        if (Test-Path -LiteralPath $welcome -PathType Leaf) { Write-TextFile $welcome 'SIGAP Ambulans' }
        [void](Invoke-Proc -File $script:Composer -ArgList @('dump-autoload') -EnvVars $cenv -TimeoutSeconds 600)
        Write-Log 'Kerangka Laravel 13 siap.' 'OK'
        $needDompdf = -not (Test-Path -LiteralPath (Join-P $script:Root @('vendor', 'barryvdh', 'laravel-dompdf')) -PathType Container)
        $needNpm = -not (Test-NpmReady)
    }

    if ($needDompdf) {
        if (-not $script:Composer) { Write-Log 'composer tidak ditemukan; dibutuhkan untuk memasang dompdf.' 'ERROR'; return $false }
        Write-Log 'Memasang barryvdh/laravel-dompdf (untuk laporan PDF)...' 'STEP'
        $r = Invoke-Proc -File $script:Composer -ArgList @('require', 'barryvdh/laravel-dompdf', '--no-interaction') -EnvVars $cenv -TimeoutSeconds 1800 -HeartbeatSeconds 60 -HeartbeatLabel 'composer'
        if ($r.ExitCode -ne 0) {
            Write-Log ('composer require dompdf gagal (kode ' + $r.ExitCode + '): ' + (Get-TailText ($r.Stdout + [Environment]::NewLine + $r.Stderr + $r.Error) 15)) 'ERROR'
            return $false
        }
        Write-Log 'dompdf terpasang.' 'OK'
    }

    if ($needNpm) {
        Write-Log 'Memasang paket npm (Tailwind 4, Vite, font, ikon, Leaflet, linter DESIGN.md) - beberapa menit, butuh internet...' 'STEP'
        $r = Invoke-Proc -File $script:Npm -ArgList @('install', '--no-fund', '--no-audit') -EnvVars $nenv -TimeoutSeconds 1800 -HeartbeatSeconds 60 -HeartbeatLabel 'npm'
        if ($r.ExitCode -ne 0) {
            Write-Log ('npm install gagal (kode ' + $r.ExitCode + '): ' + (Get-TailText ($r.Stdout + [Environment]::NewLine + $r.Stderr + $r.Error) 15)) 'ERROR'
            return $false
        }
        $r = Invoke-Proc -File $script:Npm -ArgList @('install', '--no-fund', '--no-audit', '--save-dev', '@google/design.md', '@fontsource-variable/roboto', 'lucide-static', 'yaml') -EnvVars $nenv -TimeoutSeconds 1800 -HeartbeatSeconds 60 -HeartbeatLabel 'npm'
        if ($r.ExitCode -ne 0) {
            Write-Log ('npm install (dev) gagal (kode ' + $r.ExitCode + '): ' + (Get-TailText ($r.Stdout + [Environment]::NewLine + $r.Stderr + $r.Error) 15)) 'ERROR'
            return $false
        }
        $r = Invoke-Proc -File $script:Npm -ArgList @('install', '--no-fund', '--no-audit', '--save-exact', 'leaflet@1.9.4', 'leaflet-routing-machine@3.2.12') -EnvVars $nenv -TimeoutSeconds 1800 -HeartbeatSeconds 60 -HeartbeatLabel 'npm'
        if ($r.ExitCode -ne 0) {
            Write-Log ('npm install (leaflet) gagal (kode ' + $r.ExitCode + '): ' + (Get-TailText ($r.Stdout + [Environment]::NewLine + $r.Stderr + $r.Error) 15)) 'ERROR'
            return $false
        }
        if (-not (Test-NpmReady)) { Write-Log 'Paket npm belum lengkap setelah install; lihat keluaran npm di atas.' 'ERROR'; return $false }
        Write-Log 'Paket npm terpasang.' 'OK'
    }

    if ($needFront) {
        Write-Log 'Menyiapkan frontend: app.css, vite.config.js, ikon, token dari DESIGN.md...' 'STEP'
        $tpl = Join-P $script:Root @('agent', 'templates')
        $cssDir = Join-P $script:Root @('resources', 'css')
        $jsDir = Join-P $script:Root @('resources', 'js')
        foreach ($d in @($cssDir, $jsDir, (Join-P $script:Root @('resources', 'icons')))) { if (-not (Test-Path -LiteralPath $d)) { [void](New-Item -ItemType Directory -Path $d -Force) } }
        Copy-Item -LiteralPath (Join-Path $tpl 'app.css') -Destination (Join-Path $cssDir 'app.css') -Force
        Copy-Item -LiteralPath (Join-Path $tpl 'vite.config.js') -Destination (Join-Path $script:Root 'vite.config.js') -Force
        if (-not (Test-Path -LiteralPath (Join-Path $cssDir 'tambahan.css'))) { Copy-Item -LiteralPath (Join-Path $tpl 'tambahan.css') -Destination (Join-Path $cssDir 'tambahan.css') }
        $petaJs = Join-Path $jsDir 'peta.js'
        if (-not (Test-Path -LiteralPath $petaJs)) { Write-TextFile $petaJs ('// Diisi agen pada T2.3 (Leaflet). Placeholder dari runner agar npm run build berjalan.' + [Environment]::NewLine) }
        $missing = @()
        foreach ($line in (Read-TextFile (Join-P $script:Root @('agent', 'icons.txt'))) -split "`r?`n") {
            $n = $line.Trim()
            if ($n.Length -eq 0 -or $n.StartsWith('#')) { continue }
            $from = Join-P $script:Root @('node_modules', 'lucide-static', 'icons', ($n + '.svg'))
            if (Test-Path -LiteralPath $from -PathType Leaf) { Copy-Item -LiteralPath $from -Destination (Join-P $script:Root @('resources', 'icons', ($n + '.svg'))) -Force }
            else { $missing += $n }
        }
        if ($missing.Count -gt 0) { Write-Log ('Ikon Lucide tidak ditemukan: ' + ($missing -join ', ')) 'ERROR'; return $false }
        $lint = Test-DesignLint
        if (-not $lint.Ok) { Write-Log $lint.Text 'ERROR'; return $false }
        Write-Log $lint.Text 'OK'
        $tok = Update-DesignTokens
        if (-not $tok.Ok) { Write-Log ('agent/tokens.mjs gagal: ' + $tok.Text) 'ERROR'; return $false }
        $b = Invoke-Proc -File $script:Npm -ArgList @('run', 'build') -EnvVars $nenv -TimeoutSeconds 900
        if ($b.ExitCode -ne 0) {
            Write-Log ('npm run build gagal: ' + (Get-BuildTail ($b.Stdout + [Environment]::NewLine + $b.Stderr + $b.Error) 20)) 'ERROR'
            return $false
        }
        Write-TextFile $frontMarker ((Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + [Environment]::NewLine)
        Write-Log 'Frontend siap (token DESIGN.md -> Tailwind -> build berhasil).' 'OK'
    }

    if ($needEnv) {
        $envPath = Join-Path $script:Root '.env'
        if (-not (Test-Path -LiteralPath $envPath -PathType Leaf)) {
            $ex = Join-Path $script:Root '.env.example'
            if (Test-Path -LiteralPath $ex -PathType Leaf) { Copy-Item -LiteralPath $ex -Destination $envPath }
            else { Write-TextFile $envPath '' }
        }
        $hostName = '127.0.0.1'; $port = '3306'; $user = 'root'; $pass = ''
        $ld = Find-LegacyDir
        if ($ld) {
            $lenvPath = Join-Path $ld '.env'
            if (Test-Path -LiteralPath $lenvPath -PathType Leaf) {
                $le = Read-DotEnv $lenvPath
                if ($le['DB_HOST']) { $hostName = [string]$le['DB_HOST'] }
                if ($le['DB_PORT']) { $port = [string]$le['DB_PORT'] }
                if ($le['DB_USERNAME']) { $user = [string]$le['DB_USERNAME'] }
                if ($le['DB_PASSWORD']) { $pass = [string]$le['DB_PASSWORD'] }
                Write-Log ('Koneksi MySQL (host/port/user/sandi) disalin dari proyek lama; nama database BARU: ' + $DbName) 'INFO'
            }
        }
        Set-DotEnvValue $envPath 'APP_NAME' 'SIGAP Ambulans'
        Set-DotEnvValue $envPath 'APP_LOCALE' 'id'
        Set-DotEnvValue $envPath 'APP_FALLBACK_LOCALE' 'id'
        Set-DotEnvValue $envPath 'APP_FAKER_LOCALE' 'id_ID'
        Set-DotEnvValue $envPath 'DB_CONNECTION' 'mysql'
        Set-DotEnvValue $envPath 'DB_HOST' $hostName
        Set-DotEnvValue $envPath 'DB_PORT' $port
        Set-DotEnvValue $envPath 'DB_DATABASE' $DbName
        Set-DotEnvValue $envPath 'DB_USERNAME' $user
        Set-DotEnvValue $envPath 'DB_PASSWORD' $pass
        if (-not [regex]::IsMatch((Read-TextFile $envPath), '(?m)^APP_KEY=.+$')) {
            [void](Invoke-Proc -File $script:Php -ArgList @('artisan', 'key:generate', '--force') -TimeoutSeconds 120)
        }
        $m = Find-Executable -Names @('mysql') -PreferDirs @($MysqlBinDir) -FallbackDirs (Get-MysqlDirs)
        if (-not $m) { Write-Log 'mysql klien tidak ditemukan; beri -MysqlBinDir "C:\laragon\bin\mysql\<versi>\bin".' 'ERROR'; return $false }
        $mv = @{}
        if ($pass) { $mv['MYSQL_PWD'] = $pass }
        $sql = 'CREATE DATABASE IF NOT EXISTS `' + $DbName + '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        $r = Invoke-Proc -File $m -ArgList @(('--host=' + $hostName), ('--port=' + $port), ('--user=' + $user), '-e', $sql) -EnvVars $mv -TimeoutSeconds 120
        if ($r.ExitCode -ne 0) {
            Write-Log ('Gagal membuat database "' + $DbName + '": ' + $r.Stderr.Trim() + ' ' + $r.Error) 'ERROR'
            Write-Log 'Pastikan MySQL (Laragon: Start All) menyala dan kredensial benar, lalu jalankan ulang.' 'ERROR'
            return $false
        }
        # Database baru dan kosong: jalankan migrasi bawaan kerangka supaya gate "migrasi tertunda" tidak merah sejak awal.
        $mg = Invoke-Proc -File $script:Php -ArgList @('artisan', 'migrate', '--force') -TimeoutSeconds 600
        if ($mg.ExitCode -ne 0) {
            Write-Log ('php artisan migrate gagal pada database baru: ' + (Get-TailText ($mg.Stdout + [Environment]::NewLine + $mg.Stderr) 12)) 'ERROR'
            return $false
        }
        Write-Log ('Database "' + $DbName + '" siap dan termigrasi; .env proyek baru sudah diatur.') 'OK'
    }

    Import-Legacy
    Write-TextFile $marker ((Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + [Environment]::NewLine)
    return $true
}

function Test-Konsistensi {
    $res = [pscustomobject]@{ Ok = $true; Count = 0; Text = '' }
    $raw = Read-TextFile (Join-P $script:AgentDir @('konsistensi.json'))
    if (-not $raw) { $res.Ok = $false; $res.Text = 'GAGAL: agent/konsistensi.json tidak ditemukan.'; return $res }
    $cfg = $null
    try { $cfg = $raw | ConvertFrom-Json } catch { $res.Ok = $false; $res.Text = 'GAGAL: agent/konsistensi.json tidak valid: ' + $_.Exception.Message; return $res }
    $nl = [Environment]::NewLine
    $sb = New-Object System.Text.StringBuilder
    $total = 0
    foreach ($rule in @($cfg.aturan)) {
        $hits = New-Object System.Collections.ArrayList
        $seen = @{}
        foreach ($folder in @($rule.folder)) {
            $dir = $script:Root
            foreach ($part in ([string]$folder).Split('/')) { $dir = Join-Path $dir $part }
            if (-not (Test-Path -LiteralPath $dir -PathType Container)) { continue }
            foreach ($f in @(Get-ChildItem -LiteralPath $dir -Recurse -File -ErrorAction SilentlyContinue)) {
                $rel = $f.FullName.Substring($script:Root.Length).TrimStart('\', '/').Replace('\', '/')
                if ($seen.ContainsKey($rel)) { continue }
                $seen[$rel] = $true
                $okExt = $false
                foreach ($e in @($rule.ekstensi)) { if ($f.Name.EndsWith([string]$e, [StringComparison]::OrdinalIgnoreCase)) { $okExt = $true; break } }
                if (-not $okExt) { continue }
                $skip = $false
                foreach ($k in @($rule.kecuali)) { if ($k -and [regex]::IsMatch($rel, [string]$k)) { $skip = $true; break } }
                if ($skip) { continue }
                $lines = $null
                try { $lines = [IO.File]::ReadAllLines($f.FullName) } catch { continue }
                if ($rule.tipe -eq 'awal') {
                    $first = $null
                    foreach ($ln in $lines) {
                        $t = $ln.Trim()
                        if ($t.Length -eq 0) { continue }
                        if ($t -match '^\{\{--.*--\}\}$') { continue }
                        $first = $ln; break
                    }
                    if ($null -eq $first -or -not [regex]::IsMatch($first, [string]$rule.pola)) { [void]$hits.Add($rel + ': baris pertama bukan layout') }
                } else {
                    for ($i = 0; $i -lt $lines.Count; $i++) {
                        if ([regex]::IsMatch($lines[$i], [string]$rule.pola)) {
                            $snippet = $lines[$i].Trim()
                            if ($snippet.Length -gt 110) { $snippet = $snippet.Substring(0, 110) + '...' }
                            [void]$hits.Add($rel + ':' + ($i + 1) + ': ' + $snippet)
                        }
                    }
                }
            }
        }
        if ($hits.Count -gt 0) {
            $total += $hits.Count
            [void]$sb.AppendLine('[' + $rule.id + '] ' + $rule.pesan)
            $show = [Math]::Min(12, $hits.Count)
            for ($i = 0; $i -lt $show; $i++) { [void]$sb.AppendLine('  ' + $hits[$i]) }
            if ($hits.Count -gt $show) { [void]$sb.AppendLine('  ... dan ' + ($hits.Count - $show) + ' lainnya') }
        }
    }
    $res.Count = $total
    if ($total -gt 0) { $res.Ok = $false; $res.Text = 'GAGAL: konsistensi - ' + $total + ' pelanggaran (ubah kodenya; aturan ada di agent/konsistensi.json):' + $nl + $sb.ToString() }
    else { $res.Text = 'LULUS: konsistensi (0 pelanggaran).' }
    return $res
}

# ---------------------------------------------------------------- preflight

function Start-Preflight {
    $okAll = $true

    $script:Php = Select-Php
    $script:Git = Find-Executable -Names @('git') -FallbackDirs @('C:\Program Files\Git\cmd', 'C:\Program Files (x86)\Git\cmd')
    $script:Claude = Find-Executable -Names @('claude') -FallbackDirs @((Join-P $env:USERPROFILE @('.local', 'bin')), (Join-P $env:APPDATA @('npm')))
    $script:Composer = Find-Executable -Names @('composer') -FallbackDirs @('C:\ProgramData\ComposerSetup\bin', (Join-P $env:APPDATA @('Composer', 'vendor', 'bin')), 'C:\laragon\bin\composer')
    $script:Node = Find-Executable -Names @('node') -FallbackDirs @('C:\Program Files\nodejs', 'C:\laragon\bin\nodejs\*')
    if ($script:Node) { Add-ToPath (Split-Path -Parent $script:Node); $script:Npm = Find-Executable -Names @('npm') -PreferDirs @((Split-Path -Parent $script:Node)) }
    if (-not $script:Php) { $okAll = $false }
    foreach ($pair in @(@('git', $script:Git), @('claude', $script:Claude), @('composer', $script:Composer), @('node', $script:Node), @('npm', $script:Npm))) {
        if ($pair[0] -eq 'node' -and $pair[1]) { continue }
        if ($pair[1]) { Write-Log ('{0}: {1}' -f $pair[0], $pair[1]) 'OK' }
        else { Write-Log ('{0} tidak ditemukan di PATH.' -f $pair[0]) 'ERROR'; $okAll = $false }
    }
    if ($okAll -and -not (Test-NodeOk)) { $okAll = $false }
    if (-not $okAll) { return $false }

    if (Test-LegacyProjectFolder) {
        Write-Log 'Folder ini tampak PROYEK LAMA (ada RumahSakit / migrasi rumah_sakit). Agen v2 tidak boleh dijalankan di sini. Ekstrak paket v2 ke folder BARU yang terpisah.' 'ERROR'
        return $false
    }

    foreach ($f in @(@('DESIGN.md'), @('docs', 'RANCANGAN_ALUR.md'), @('docs', 'KIT_UI.md'), @('docs', 'PROGRESS.md'), @('agent', 'ITERASI.md'), @('agent', 'agent-settings.json'), @('agent', 'konsistensi.json'), @('agent', 'tokens.mjs'), @('agent', 'icons.txt'), @('agent', 'templates', 'app.css'), @('agent', 'templates', 'tambahan.css'), @('agent', 'templates', 'vite.config.js'))) {
        if (-not (Test-Path -LiteralPath (Join-P $script:Root $f) -PathType Leaf)) {
            Write-Log ('Berkas paket hilang: ' + ($f -join '/')) 'ERROR'
            return $false
        }
    }
    if (-not (Test-Path -LiteralPath (Join-Path $script:Root 'CLAUDE.agent.md') -PathType Leaf)) {
        Write-Log 'Berkas paket hilang: CLAUDE.agent.md' 'ERROR'
        return $false
    }

    $mods = (Invoke-Proc -File $script:Php -ArgList @('-m') -TimeoutSeconds 60).Stdout
    foreach ($m in @('pdo_sqlite', 'sqlite3', 'pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'curl')) {
        if ($mods -match ('(?im)^' + $m + '\s*$')) { Write-Log ('PHP ekstensi ' + $m + ': ada') 'OK' }
        else {
            Write-Log ('PHP ekstensi ' + $m + ' TIDAK aktif pada ' + $script:Php + '. Buka php.ini (php --ini), hapus tanda ; pada baris extension=' + $m + ' lalu jalankan ulang.') 'ERROR'
            $okAll = $false
        }
    }
    if (-not $okAll) { return $false }

    if (-not (Initialize-Project)) { return $false }

    $hasProject = (Test-Path -LiteralPath (Join-Path $script:Root 'artisan') -PathType Leaf) -and (Test-Path -LiteralPath (Join-Path $script:Root '.env') -PathType Leaf)
    if ($CheckOnly -and -not $hasProject) {
        Write-Log 'Proyek belum dibuat; pemeriksaan lanjutan (phpunit, backup, git) dilakukan pada run sungguhan.' 'INFO'
        return $true
    }

    if (-not (Test-Path -LiteralPath (Join-P $script:Root @('vendor', 'autoload.php')) -PathType Leaf)) {
        Write-Log 'Folder vendor belum ada. Jalankan "composer install" di folder proyek dulu.' 'ERROR'
        return $false
    }

    if (-not (Set-PhpunitGuard)) { return $false }
    Set-ClaudeMdImport

    if ($SkipDbBackup) { Write-Log 'Backup database DILEWATI (-SkipDbBackup).' 'WARN' }
    elseif (-not (Backup-Database)) { return $false }

    if (-not (Initialize-GitBranch)) { return $false }
    return $true
}

# ---------------------------------------------------------------- utama

$script:LogDir = Join-Path $script:AgentDir 'logs'
$script:BackupDir = Join-Path $script:AgentDir 'backup'
foreach ($d in @($script:LogDir, $script:BackupDir)) {
    if (-not (Test-Path -LiteralPath $d)) { [void](New-Item -ItemType Directory -Path $d -Force) }
}
$script:RunLog = Join-Path $script:LogDir ('run-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.log')
$script:SettingsPath = Join-Path $script:AgentDir 'agent-settings.json'
Set-Location -LiteralPath $script:Root

$lockPath = Join-Path $script:AgentDir '.lock'
$exitCode = 0
$stopReason = ''
$done = $false
$startTime = Get-Date
$totalCost = 0.0
$iterRun = 0

Write-Log '==== SIGAP Ambulans v2 - agen otonom ====' 'STEP'
Write-Log ('Folder proyek: ' + $script:Root) 'INFO'

if (Test-Path -LiteralPath $lockPath) {
    $oldPidText = (Read-TextFile $lockPath).Trim()
    $oldPid = 0
    if ([int]::TryParse($oldPidText, [ref]$oldPid) -and $oldPid -gt 0 -and (Get-Process -Id $oldPid -ErrorAction SilentlyContinue)) {
        Write-Log ('Agen lain masih berjalan (PID ' + $oldPid + '). Tutup dulu atau tunggu selesai.') 'ERROR'
        exit 4
    }
}

if (-not (Start-Preflight)) {
    Write-Log 'Preflight gagal. Perbaiki pesan ERROR di atas lalu jalankan ulang. Tidak ada yang dijalankan oleh agen.' 'ERROR'
    exit 4
}
if ($CheckOnly) {
    Write-Log 'Mode -CheckOnly: semua pemeriksaan lulus. Tidak ada perubahan yang dibuat dan agen tidak dijalankan.' 'OK'
    exit 0
}

Write-TextFile $lockPath ([string]$PID)
Set-KeepAwake $true

try {
    $iterBase = @(Get-ChildItem -LiteralPath $script:LogDir -Filter 'iter-*.json' -ErrorAction SilentlyContinue).Count
    $failures = 0
    $limitWaits = 0
    $stall = 0
    $prompt = 'Run one agent iteration for this project. First read agent/ITERASI.md and follow it exactly.'

    Write-Log ('Anggaran: maks {0} iterasi, ${1} per iterasi, ${2} total. Mulai dalam 10 detik (Ctrl+C untuk batal).' -f $MaxIterations, $BudgetPerIterationUsd, $TotalBudgetUsd) 'INFO'
    Start-Sleep -Seconds 10

    # Kasus lanjut: PROGRESS sudah menyatakan SELESAI -> verifikasi saja.
    $prog = Get-Progress
    if ($prog.Status -eq 'SELESAI') {
        $g0 = Invoke-Gates $iterBase
        if ($prog.Unchecked -eq 0 -and $g0.Ok) { $done = $true; Write-Log 'PROGRESS sudah SELESAI dan terverifikasi.' 'OK' }
        else {
            Set-ProgressStatusBerjalan
            Write-LastGate $iterBase $g0 ('Status SELESAI ditolak runner: item belum dicentang = ' + $prog.Unchecked + '. Status dikembalikan ke BERJALAN.')
        }
    }
    $fp = Get-Fingerprint

    $iter = 0
    while (-not $done -and $iter -lt $MaxIterations) {
        $prog = Get-Progress
        if ($prog.Status -eq 'BLOKIR') { $stopReason = 'Agen melaporkan hambatan: ' + $prog.Reason; $exitCode = 2; break }
        if ($totalCost -ge $TotalBudgetUsd) { $stopReason = ('Anggaran total tercapai ($' + $totalCost + ').'); $exitCode = 3; break }

        $iter++
        $iterNo = $iterBase + $iterRun + 1
        $iterRun++
        Write-Log ('=== Iterasi {0} (sesi ke-{1} pada run ini) | tahap/item belum selesai: {2} ===' -f $iterNo, $iter, $prog.Unchecked) 'STEP'

        $cargs = @('-p', $prompt,
                   '--permission-mode', 'acceptEdits',
                   '--settings', $script:SettingsPath,
                   '--max-turns', ([string]$MaxTurns),
                   '--max-budget-usd', $BudgetPerIterationUsd.ToString([Globalization.CultureInfo]::InvariantCulture),
                   '--output-format', 'json')
        if ($Model) { $cargs += @('--model', $Model) }

        $t0 = Get-Date
        $r = Invoke-Proc -File $script:Claude -ArgList $cargs -TimeoutSeconds ($IterationTimeoutMinutes * 60) -HeartbeatSeconds 300 -HeartbeatLabel 'Agen'
        $minutes = [Math]::Round(((Get-Date) - $t0).TotalMinutes, 1)
        $tag = $iterNo.ToString('000')
        Write-TextFile (Join-Path $script:LogDir ('iter-' + $tag + '.json')) $r.Stdout
        Write-TextFile (Join-Path $script:LogDir ('iter-' + $tag + '.err.txt')) ($r.Stderr + $r.Error)

        $j = $null
        try {
            if ($r.Stdout.Trim().Length -gt 0) {
                $parsed = $r.Stdout | ConvertFrom-Json
                if ($parsed -is [array]) {
                    foreach ($item in $parsed) { if ((Get-Prop $item 'type' '') -eq 'result') { $j = $item } }
                } else { $j = $parsed }
            }
        } catch { $j = $null }

        $subtype = [string](Get-Prop $j 'subtype' '')
        $isErr = [bool](Get-Prop $j 'is_error' $false)
        $cost = 0.0
        try { $cost = [double](Get-Prop $j 'total_cost_usd' 0) } catch { $cost = 0.0 }
        $turns = Get-Prop $j 'num_turns' '?'
        $resText = [string](Get-Prop $j 'result' '')
        $totalCost += $cost

        $normalEnd = ($null -ne $j) -and ((-not $isErr) -or ($subtype -eq 'error_max_turns') -or ($subtype -eq 'error_max_budget_usd'))
        if ($r.TimedOut) { $normalEnd = $false }

        $kind = 'ok'
        if (-not $normalEnd) {
            $blob = $r.Stdout + ' ' + $r.Stderr + ' ' + $r.Error + ' ' + $resText
            if ($r.TimedOut) { $kind = 'timeout' }
            elseif (Test-AuthText $blob) { $kind = 'auth' }
            elseif (Test-LimitText $blob) { $kind = 'limit' }
            else { $kind = 'gagal' }
        }

        Write-Log ('Iterasi {0} selesai: {1} | {2} menit | {3} giliran | ${4:N2} | subtipe: {5}' -f $iterNo, $kind, $minutes, $turns, $cost, $subtype) 'INFO'
        $sum = '## Iterasi ' + $iterNo + ' - ' + (Get-Date -Format 'yyyy-MM-dd HH:mm') + ' - ' + $kind + ' - ' + $minutes + ' menit - $' + $cost + [Environment]::NewLine + $resText + [Environment]::NewLine + [Environment]::NewLine
        [IO.File]::AppendAllText((Join-Path $script:LogDir 'ringkasan.md'), $sum, $script:Utf8NoBom)

        if ($kind -eq 'auth') {
            $stopReason = 'Claude Code belum login / token kedaluwarsa. Jalankan "claude" sekali di PowerShell, login, lalu jalankan ulang skrip ini (lanjut otomatis).'
            $exitCode = 4; break
        }
        if ($kind -eq 'limit') {
            $limitWaits++
            $iter--
            if ($limitWaits -gt $MaxLimitWaits) { $stopReason = 'Limit pemakaian bertahan terlalu lama.'; $exitCode = 3; break }
            Wait-Minutes $LimitWaitMinutes ('limit pemakaian/kapasitas (' + $limitWaits + '/' + $MaxLimitWaits + ')')
            continue
        }
        $limitWaits = 0

        $dirty = ((Invoke-Git @('status', '--porcelain')).Stdout.Trim().Length -gt 0)
        if ($normalEnd -or $dirty) {
            $gate = Invoke-Gates $iterNo
            $h = Save-Checkpoint ('chore(agent): checkpoint iterasi ' + $iterNo + ' (gate ' + $gate.Verdict + ')')
            if ($h) { Write-Log ('Checkpoint commit: ' + $h) 'OK' }
            Write-Log ('Gate: ' + $gate.Verdict) $(if ($gate.Ok) { 'OK' } else { 'WARN' })
            Write-LastGate $iterNo $gate
        } else {
            $gate = $null
        }

        if (-not $normalEnd) {
            $failures++
            Write-Log ('Iterasi gagal (' + $kind + '); gagal beruntun: ' + $failures + '/' + $MaxFailures) 'WARN'
            if ($failures -ge $MaxFailures) { $stopReason = 'Gagal beruntun ' + $failures + ' kali. Lihat agent\logs\iter-' + $tag + '.err.txt'; $exitCode = 4; break }
            Wait-Minutes ([int][Math]::Min(30, [Math]::Pow(2, $failures - 1))) 'jeda sebelum mencoba lagi'
            continue
        }
        $failures = 0

        $prog = Get-Progress
        if ($prog.Status -eq 'BLOKIR') { $stopReason = 'Agen melaporkan hambatan: ' + $prog.Reason; $exitCode = 2; break }
        if ($prog.Status -eq 'SELESAI') {
            if ($prog.Unchecked -eq 0 -and $gate -and $gate.Ok) { $done = $true; break }
            $why = 'Status SELESAI ditolak runner: item belum dicentang = ' + $prog.Unchecked + '; gate = ' + $gate.Verdict + '. Status dikembalikan ke BERJALAN; selesaikan sisa pekerjaan.'
            Write-Log $why 'WARN'
            Set-ProgressStatusBerjalan
            Write-LastGate $iterNo $gate $why
        }

        $newFp = Get-Fingerprint
        if ($newFp -eq $fp) {
            $stall++
            Write-Log ('Tidak ada kemajuan (commit & PROGRESS tidak berubah): ' + $stall + '/2') 'WARN'
            if ($stall -ge 2) { $stopReason = 'Macet: dua iterasi berturut-turut tanpa commit/perubahan PROGRESS.'; $exitCode = 3; break }
        } else { $stall = 0 }
        $fp = $newFp
    }

    if (-not $done -and -not $stopReason -and $iter -ge $MaxIterations) {
        $stopReason = 'Batas iterasi (' + $MaxIterations + ') tercapai. Jalankan ulang skrip untuk melanjutkan.'
        $exitCode = 3
    }
}
finally {
    Set-KeepAwake $false
    if (Test-Path -LiteralPath $lockPath) { Remove-Item -LiteralPath $lockPath -Force -ErrorAction SilentlyContinue }
}

# ---------------------------------------------------------------- ringkasan akhir

$prog = Get-Progress
$elapsed = (Get-Date) - $startTime
Write-Log '==== RINGKASAN ====' 'STEP'
if ($done) { Write-Log 'STATUS: SELESAI dan terverifikasi runner (tes hijau, tidak ada item tersisa).' 'OK' }
else { Write-Log ('STATUS: BERHENTI - ' + $stopReason) 'WARN' }
Write-Log ('Item: {0} selesai, {1} tersisa, {2} buntu ([!]).' -f $prog.Checked, $prog.Unchecked, $prog.Blocked) 'INFO'
Write-Log ('Waktu: {0:N0} menit | Perkiraan biaya run ini: ${1:N2}' -f $elapsed.TotalMinutes, $totalCost) 'INFO'
$lg = Invoke-Git @('log', '--oneline', '-n', '12')
Write-Log ('Commit terakhir di branch ' + $Branch + ':' + [Environment]::NewLine + $lg.Stdout.Trim()) 'INFO'
$hasil = Join-P $script:Root @('docs', 'HASIL_AKHIR.md')
if (Test-Path -LiteralPath $hasil -PathType Leaf) { Write-Log ('Baca hasil akhir: ' + $hasil) 'OK' }
Write-Log ('Log lengkap: ' + $script:RunLog + ' | ringkasan per iterasi: agent\logs\ringkasan.md') 'INFO'
if (-not $done) { Write-Log 'Untuk melanjutkan, jalankan perintah yang sama lagi (state ada di docs\PROGRESS.md).' 'INFO' }
else {
    try { [Console]::Beep(880, 250); [Console]::Beep(1175, 400) } catch { }
    if ($script:OnWindows -and (Test-Path -LiteralPath $hasil -PathType Leaf)) { try { Invoke-Item -LiteralPath $hasil } catch { } }
}
exit $exitCode
