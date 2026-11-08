<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisAmbulans;
use App\Enums\Role;
use App\Enums\StatusAmbulans;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AmbulansRequest;
use App\Models\Ambulans;
use App\Models\Faskes;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AmbulansController extends Controller
{
    public function index(Request $request): View
    {
        $faskesId = (int) $request->query('faskes_id');
        $jenis = JenisAmbulans::tryFrom((string) $request->query('jenis'));
        $status = StatusAmbulans::tryFrom((string) $request->query('status'));

        $daftar = Ambulans::with(['faskes', 'sopirTetap'])
            ->when($faskesId > 0, fn (Builder $q) => $q->where('faskes_id', $faskesId))
            ->when($jenis, fn (Builder $q) => $q->where('jenis', $jenis->value))
            ->when($status, fn (Builder $q) => $q->where('status', $status->value))
            ->orderBy('plat_nomor')
            ->paginate(config('sigap.per_halaman'))
            ->withQueryString();

        return view('admin.ambulans.index', [
            'daftar' => $daftar,
            'faskesId' => $faskesId ?: null,
            'jenis' => $jenis,
            'status' => $status,
            'adaFilter' => $faskesId > 0 || $jenis !== null || $status !== null,
            'opsiFaskes' => Faskes::orderBy('nama')->pluck('nama', 'id')->all(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Ambulans(['status' => StatusAmbulans::Tersedia]), true);
    }

    public function store(AmbulansRequest $request): RedirectResponse
    {
        $ambulans = Ambulans::create($request->validated() + ['status' => StatusAmbulans::Tersedia->value]);

        return redirect()->route('admin.ambulans.index')->with('sukses', 'Ambulans '.$ambulans->plat_nomor.' berhasil ditambahkan.');
    }

    public function edit(Ambulans $ambulans): View
    {
        return $this->form($ambulans, false);
    }

    public function update(AmbulansRequest $request, Ambulans $ambulans): RedirectResponse
    {
        $data = $request->validated();
        if ($ambulans->status === StatusAmbulans::Bertugas) {
            unset($data['status']);
        }
        $ambulans->update($data);

        return redirect()->route('admin.ambulans.index')->with('sukses', 'Ambulans '.$ambulans->plat_nomor.' berhasil disimpan.');
    }

    private function form(Ambulans $ambulans, bool $baru): View
    {
        $sopir = User::with('faskes')->where('role', Role::Sopir->value)->whereNotNull('faskes_id')->orderBy('name')->get();
        $opsiSopir = [];
        foreach ($sopir as $s) {
            $opsiSopir[$s->faskes->nama][$s->id] = $s->name;
        }
        ksort($opsiSopir);

        return view('admin.ambulans.form', [
            'ambulans' => $ambulans,
            'baru' => $baru,
            'opsiFaskes' => Faskes::aktif()->orderBy('nama')->pluck('nama', 'id')->all(),
            'opsiSopir' => $opsiSopir,
        ]);
    }
}
