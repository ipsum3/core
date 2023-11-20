<?php

namespace Ipsum\Core\app\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoLink implements Rule
{

    protected $ip;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return stristr($value, 'https://') === false and stristr($value, 'http://') === false and stristr($value, 'www.') === false and stristr($value, 'hxxp://') === false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Pour éviter le spam, il n\'est pas autorisé de poster des liens';
    }
}
