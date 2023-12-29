<?php

namespace App\Http\Requests\InfoType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInfoTypeRequest extends FormRequest
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
            'title' => 'nullable|max:255',
            // 'type' => 'required|max:255',
            'description' => 'nullable',
            'references' => 'nullable',
        ];
    }
}
