<?php

namespace App\Modules\Meteran\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMeteranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'pelanggan_id' => [
                'required',
                'integer',
                Rule::exists('pelanggan', 'id')->whereNull('deleted_at'),
            ],

            // Format YYYY-MM, max bulan saat ini (tidak bisa input masa depan)
            'periode' => [
                'required',
                'string',
                'regex:/^\d{4}-(0[1-9]|1[0-2])$/',
                'before_or_equal:' . now()->format('Y-m'),
            ],

            'kubik_awal'  => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'kubik_akhir' => ['required', 'numeric', 'min:0', 'max:99999.99',
                              'gte:kubik_awal'],

            'foto_meteran' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'catatan'      => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'pelanggan_id.required'   => 'Pilih pelanggan terlebih dahulu.',
            'pelanggan_id.exists'     => 'Pelanggan tidak ditemukan atau sudah dihapus.',
            'periode.required'        => 'Periode wajib diisi.',
            'periode.regex'           => 'Format periode harus YYYY-MM. Contoh: 2024-01.',
            'periode.before_or_equal' => 'Tidak dapat menginput meteran untuk periode mendatang.',
            'kubik_awal.required'     => 'Kubik awal wajib diisi.',
            'kubik_awal.min'          => 'Kubik awal tidak boleh negatif.',
            'kubik_akhir.required'    => 'Kubik akhir wajib diisi.',
            'kubik_akhir.gte'         => 'Kubik akhir tidak boleh lebih kecil dari kubik awal.',
            'foto_meteran.image'      => 'File harus berupa gambar (JPEG, PNG, WebP).',
            'foto_meteran.max'        => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalisasi periode: tambahkan 0 jika bulan 1 digit (3 → 03)
        if ($this->periode && preg_match('/^\d{4}-\d{1}$/', $this->periode)) {
            [$y, $m] = explode('-', $this->periode);
            $this->merge(['periode' => $y . '-' . str_pad($m, 2, '0', STR_PAD_LEFT)]);
        }
    }
}
