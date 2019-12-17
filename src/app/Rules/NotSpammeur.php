<?php

namespace Ipsum\Core\app\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotSpammeur implements Rule
{

    protected $ip;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($ip = null)
    {
        $this->ip = !empty($ip) ? $ip : \Request::getClientIp();
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
        $adresse = 'http://www.stopforumspam.com/api?';
        $query = array(
            'confidence' => 'true',
            'f' => 'xmldom',
        );

        $query['email'] = urlencode($value);
        $query['ip'] = urlencode($this->ip);

        foreach($query as $key => $value) {
            $adresse .= $key.'='.$value.'&';
        }

        $xml_string = file_get_contents($adresse);
        if ($xml_string) {
            $xml = new \SimpleXMLElement($xml_string);
            if ($xml->success == 1) {
                foreach ($xml->children() as $value) {
                    if ($value->appears == "1" and  $value->confidence >= 0) {
                        // spammeur detecté
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Vous avez été detecté comme spammeur.';
    }
}
