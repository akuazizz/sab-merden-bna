<?php

namespace App\Modules\Meteran\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeteranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            // Saat koreksi: pelanggan_id dan periode TIDAK bisa diubah
            // Hanya nilai kubik dan catatan yang bisa dikoreksi

            'kubik_awal'   => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'kubik_akhir'  => ['required', 'numeric', 'min:0', 'max:99999.99',
                               'gte:kubik_awal'],
            'foto_meteran' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'catatan'      => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'kubik_awal.required'  => 'Kubik awal wajib diisi.',
            'kubik_awal.min'       => 'Kubik awal tidak boleh negatif.',
            'kubik_akhir.required' => 'Kubik akhir wajib diisi.',
            'kubik_akhir.gte'      => 'Kubik akhir tidak boleh lebih kecil dari kubik awal.',
            'foto_meteran.image'   => 'File harus berupa gambar (JPEG, PNG, WebP).',
            'foto_meteran.max'     => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
