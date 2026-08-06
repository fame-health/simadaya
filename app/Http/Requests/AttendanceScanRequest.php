<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMahasiswa() ?? false;
    }

    public function rules(): array
    {
        return [
            'payload' => ['required', 'string'],
            'browser' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
