<?php

namespace App\Http\Requests\InfoEntry;

use Illuminate\Foundation\Http\FormRequest;

class DeleteInfoEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'folder_id'=>'nullable|integer',
            'source_id'=>'nullable|integer',
        ];
    }
}
