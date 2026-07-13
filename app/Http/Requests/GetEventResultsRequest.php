<?php

namespace App\Http\Requests;

class GetEventResultsRequest extends CursorPaginationRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
    }
}
