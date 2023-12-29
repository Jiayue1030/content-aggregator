<?php

namespace App\Http\Requests\InfoEntry;

use Illuminate\Foundation\Http\FormRequest;

class AddInfoEntryRequest extends FormRequest
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
            'title'=>'nullable|max:255',
            'description'=>'nullable',
            'contents'=>'nullable',
        ];
    }
}
