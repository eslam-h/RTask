<?php

namespace App\Http\Requests\ActivityRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ActivityPostRequest Class responsible for activity form request validations
 * @package App\Http\Requests\ActivityRequest
 * @author Amira Sherif <a.sherif@shiftebusiness.com>
 */
class ActivityPostRequest extends FormRequest
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
            "default-name" => "required|unique:activities,name",
            "photo" => "required|image|mimes:jpeg,jpg,png",
            "icon" => "required|mimetypes:text/plain,image/png,image/svg",
//            mimes:text/xml,svg,html",
            "color" => "required",
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     * @return array
     */
    public function messages()
    {
        return [
            'default-name.required' => 'The default name field is required.',
            'default-name.unique' => 'The default name must be unique.',
            'photo.required' => 'The activity background photo is required.',
            'icon.required' => 'The activity icon is required.',
            'photo.image' => 'The activity background image must be with an extension of .jpg, .jpeg or .png.',
            'icon.mimetypes' => 'The activity icon must be a .svg file or a .png file.',
            'color.required' => 'The activity background overlay color is required.',
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
