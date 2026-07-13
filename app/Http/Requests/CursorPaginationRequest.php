<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\Cursor;

class CursorPaginationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cursor' => ['nullable', 'string'],
        ];
    }

    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $validator) {
            $cursor = $this->input('cursor');

            if (! empty($cursor) && Cursor::fromEncoded($cursor) === null) {
                $validator->errors()->add('cursor', 'The cursor is invalid.');
            }
        });
    }
}
