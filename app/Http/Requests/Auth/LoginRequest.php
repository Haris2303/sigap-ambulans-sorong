<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public const MAKS_PERCOBAAN = 5;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /** Masuk dengan pembatasan percobaan. Akun nonaktif ditolak dengan pesan tersendiri. */
    public function autentikasi(): void
    {
        $this->pastikanTidakTerkunci();

        $user = User::where('email', $this->string('email')->toString())->first();
        $sandiCocok = $user !== null && Hash::check($this->string('password')->toString(), $user->password);

        if ($sandiCocok && ! $user->aktif) {
            RateLimiter::hit($this->kunciPembatas());
            throw ValidationException::withMessages(['email' => trans('auth.nonaktif')]);
        }

        if (! $sandiCocok || ! Auth::attempt($this->only('email', 'password'), $this->boolean('ingat'))) {
            RateLimiter::hit($this->kunciPembatas());
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        RateLimiter::clear($this->kunciPembatas());
    }

    private function pastikanTidakTerkunci(): void
    {
        if (! RateLimiter::tooManyAttempts($this->kunciPembatas(), self::MAKS_PERCOBAAN)) {
            return;
        }

        event(new Lockout($this));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', ['seconds' => RateLimiter::availableIn($this->kunciPembatas())]),
        ]);
    }

    private function kunciPembatas(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->toString()).'|'.$this->ip());
    }
}
