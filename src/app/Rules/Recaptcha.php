<?php

namespace Ipsum\Core\app\Rules;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Validation\ValidationRule;

class Recaptcha implements ValidationRule
{

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $client = new Client();
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => env('RECAPTCHA_SECRET_KEY'),
                    'response' => $value,
                ],
            ]);

            $body = json_decode((string) $response->getBody());

            if (! is_object($body) || ! ($body->success ?? false)) {
                $fail('Le reCAPTCHA a échoué. Veuillez réessayer.');
            }
        } catch (RequestException $exception) {
            error_log('reCAPTCHA validation request failed: '.$exception->getMessage());
            $fail('Le reCAPTCHA a échoué. Veuillez réessayer.');
        }
    }
}
