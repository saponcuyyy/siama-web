<?php

namespace App\Http\Requests\Ujian;

use Illuminate\Foundation\Http\FormRequest;

class SimpanBatchJawabanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => 'required|array|min:1|max:200',
            'answers.*.soal_id' => 'required|exists:soal,id',
            'answers.*.jawaban' => 'nullable',
            'answers.*.durasi' => 'required|integer|min:0',
            'answers.*.is_ragu' => 'nullable|boolean',
        ];
    }
}
