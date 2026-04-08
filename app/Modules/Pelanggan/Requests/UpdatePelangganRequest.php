<?php

namespace App\Modules\Pelanggan\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        // Ambil ID dari route parameter — untuk ignore record saat ini di unique check
        $pelangganId = $this->route('pelanggan');

        return [
            'nama'    => ['required', 'string', 'max:100'],
            'nik'     => [
                'nullable', 'string', 'digits:16',
                Rule::unique('pelanggan', 'nik')
                    ->ignore($pelangganId)
                    ->whereNull('deleted_at'),
            ],
            'alamat'  => ['required', 'string', 'max:500'],
            'rt'      => ['nullable', 'string', 'max:5'],
            'rw'      => ['nullable', 'string', 'max:5'],
            'dusun'   => ['nullable', 'string', 'max:50'],
            'telepon' => ['nullable', 'string', 'max:20'],

            // Status hanya bisa diubah melalui endpoint deactivate/activate khusus
            // bukan lewat form update biasa
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'   => 'Nama pelanggan wajib diisi.',
            'nik.digits'      => 'NIK harus terdiri dari 16 digit angka.',
            'nik.unique'      => 'NIK sudah terdaftar untuk pelanggan lain.',
            'alamat.required' => 'Alamat wajib diisi.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->nik) {
            $this->merge(['nik' => preg_replace('/\s+/', '', $this->nik)]);
        }
    }
}
