<?php

namespace App\Modules\Pelanggan\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama'          => ['required', 'string', 'max:100'],
            'nik'           => ['nullable', 'string', 'digits:16', 'unique:pelanggan,nik'],
            'alamat'        => ['required', 'string', 'max:500'],
            'rt'            => ['nullable', 'string', 'max:5'],
            'rw'            => ['nullable', 'string', 'max:5'],
            'dusun'         => ['nullable', 'string', 'max:50'],
            'telepon'       => ['nullable', 'string', 'max:20'],
            'tanggal_daftar'=> ['nullable', 'date', 'before_or_equal:today'],

            // Akun login pelanggan — wajib diisi saat pendaftaran
            'email'         => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'          => 'Nama pelanggan wajib diisi.',
            'nik.digits'             => 'NIK harus terdiri dari 16 digit angka.',
            'nik.unique'             => 'NIK sudah terdaftar untuk pelanggan lain.',
            'alamat.required'        => 'Alamat wajib diisi.',
            'tanggal_daftar.before_or_equal' => 'Tanggal daftar tidak boleh di masa depan.',
            'email.required'         => 'Email akun wajib diisi.',
            'email.unique'           => 'Email sudah digunakan, gunakan email lain.',
            'password.required'      => 'Password wajib diisi.',
            'password.min'           => 'Password minimal 8 karakter.',
            'password.confirmed'     => 'Konfirmasi password tidak cocok.',
        ];
    }

    /**
     * Sanitasi data sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        // Normalisasi NIK — hapus spasi
        if ($this->nik) {
            $this->merge(['nik' => preg_replace('/\s+/', '', $this->nik)]);
        }
    }
}
