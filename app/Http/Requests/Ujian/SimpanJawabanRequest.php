<?php

namespace App\Http\Requests\Ujian;

use Illuminate\Foundation\Http\FormRequest;

class SimpanJawabanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'soal_id' => 'required|exists:soal,id',
            'jawaban' => 'nullable', // Bisa string atau array tergantung tipe soal
            'durasi' => 'required|integer|min:0',
            'is_ragu' => 'nullable|boolean',
        ];
    }
}
