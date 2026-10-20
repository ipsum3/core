<?php

namespace Ipsum\Core\app\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotSpammeur implements ValidationRule
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

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $notSpammeurTime = session('not_spammeur_time');

        if ($notSpammeurTime instanceof Carbon && $notSpammeurTime->greaterThan(Carbon::now()->subSecond(5))) {
            error_log('NotSpammeur rule detection ['.request()->ip().'] : formulaire envoyé trop rapidement'); // pour fail2ban
            $fail('Vous avez été détecté comme spammeur.');

            return;
        }

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

        try {
            $xml_string = file_get_contents($adresse);
            if ($xml_string) {
                $xml = new \SimpleXMLElement($xml_string);
                $success = (string) ($xml['success'] ?? $xml->success ?? '');

                if ($success === '1') {
                    foreach ($xml->children() as $entry) {
                        $appears = (string) ($entry['appears'] ?? $entry->appears ?? '');
                        $confidence = (float) ($entry['confidence'] ?? $entry->confidence ?? -1);

                        if ($appears === '1' && $confidence >= 0) {
                            // spammeur detecté
                            error_log('NotSpammeur rule detection ['.request()->ip().'] : stopforumspam detection'); // pour fail2ban
                            $fail('Vous avez été détecté comme spammeur.');

                            return;
                        }
                    }
                }
            }
        } catch (\Exception $exception) { }
    }
}
