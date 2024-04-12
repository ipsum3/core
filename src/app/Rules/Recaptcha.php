<?php

namespace Ipsum\Core\app\Rules;

use Illuminate\Contracts\Validation\Rule;
use Prologue\Alerts\Facades\Alert;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Recaptcha implements Rule
{

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
        try {
        $client = new Client();
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'form_params' => [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $value,
            ],
        ]);

        $body = json_decode((string)$response->getBody());

        return $body->success;
        } catch (RequestException $exception) {
            error_log('reCAPTCHA validation request failed: '.$exception->getMessage());
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Le reCAPTCHA a échoué. Veuillez réessayer.';
    }
}
