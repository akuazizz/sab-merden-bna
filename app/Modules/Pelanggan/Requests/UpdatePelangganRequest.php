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
            'id_pelanggan' => [
                'nullable', 'string', 'max:20',
                Rule::unique('pelanggan', 'id_pelanggan')
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
            'id_pelanggan.max'    => 'ID Pelanggan maksimal 20 karakter.',
            'id_pelanggan.unique' => 'ID Pelanggan sudah digunakan pelanggan lain.',
            'alamat.required' => 'Alamat wajib diisi.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->id_pelanggan) {
            $this->merge(['id_pelanggan' => preg_replace('/\s+/', '', $this->id_pelanggan)]);
        }
    }
}
