<?php

namespace App\Http\Requests\TagRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * TagPostRequest Class responsible for tag form request validations
 * @package App\Http\Requests\TagRequest
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class TagPostRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            "default-name" => "required"
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     * @return array
     */
    public function messages()
    {
        return [
            'default-name.required' => 'The default name field is required.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     * @return array
     */
    public function attributes()
    {
        return [
            'default-name' => 'default name',
        ];
    }
}
