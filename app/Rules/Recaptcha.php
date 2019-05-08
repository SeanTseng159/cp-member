<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Exception;
use GuzzleHttp\Client;

class Recaptcha implements Rule
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
        try {
            if ( ! config('services.recaptcha.apiKey')) return true;

            $client = new Client;
            $response = $client->post(config('services.recaptcha.verifyUrl'), [
                'form_params' => [
                    'secret' => config('services.recaptcha.apiKey'),
                    'response' => $value,
                ],
            ]);

            $responseBody = json_decode($response->getBody());

            return ($response->getStatusCode() === 200 && optional($responseBody)->success);
        } catch (Exception $e) {
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
        return 'The :attribute must be legal.';
    }
}
