<?php

namespace App\CustomValidationRules;

use Illuminate\Contracts\Validation\Rule;

/**
 * TripImageRatio Class validate trip image dimensions to be with ration 16/9
 * @package App\CustomValidationRules
 * @author Eslam
 */
class TripImageRatio implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return strtoupper($value) === $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be uppercase.';
    }
}