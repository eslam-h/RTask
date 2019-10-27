<?php

namespace App\Http\Requests\TripRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * TripRequest Class responsible for trip request
 * @package App\Http\Requests\TripRequest
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class TripRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        return [
            "default-name" => "required",
//            "photo" => "required|dimensions:ratio=16/9",
            "location" => "required",
            "activity" => "required",
            "tour-guide-language" => "required",
            "confirmation-type" => "required",
            "start-date" => "required|date_format:m/d/Y",
            "end-date" => "required|date_format:m/d/Y",
            "start-time" => "required|date_format:g:i A",
            "end-time" => "required|date_format:g:i A"
        ];
    }
}