<?php

namespace App\Modules\Tagihan\Requests;

use App\Modules\Tagihan\Enums\TagihanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoidTagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'alasan.required' => 'Alasan pembatalan wajib diisi.',
            'alasan.min'      => 'Alasan minimal 10 karakter.',
        ];
    }
}
