<?php

namespace Ipsum\Core\app\Rules;

use Illuminate\Contracts\Validation\Rule;

class Akismet implements Rule
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
    public function passes( $attribute, $value )
    {
        $request = 'blog=' . $_SERVER['SERVER_NAME'] .
            '&user_ip='. urlencode( $this->ip ) .
            '&comment_author_email='. urlencode( $value );
        /*'&referrer='. urlencode($data['referrer']) .
        '&permalink='. urlencode($data['permalink']) .
        '&comment_type='. urlencode($data['comment_type']) .
        '&comment_author='. urlencode($data['comment_author']) .
        '&comment_author_url='. urlencode($data['comment_author_url']) .
        '&comment_content='. urlencode($data['comment_content']);*/

        // Spam adress => akismet-guaranteed-spam@example.com

        $host = $http_host = env('AKISMET_KEY').'.rest.akismet.com';
        $path = '/1.1/comment-check';
        $port = 443;
        $akismet_ua = "WordPress/4.4.1 | Akismet/3.1.7";
        $content_length = strlen( $request );
        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $http_request .= "Content-Length: {$content_length}\r\n";
        $http_request .= "User-Agent: {$akismet_ua}\r\n";
        $http_request .= "\r\n";
        $http_request .= $request;
        $response = '';
        if( false != ( $fs = @fsockopen( 'ssl://' . $http_host, $port, $errno, $errstr, 10 ) ) ) {

            fwrite( $fs, $http_request );

            while ( !feof( $fs ) ) {
                $response .= fgets( $fs, 1160 ); // One TCP-IP packet
            }
            fclose( $fs );

            $response = explode( "\r\n\r\n", $response, 2 );

            // Si repsonse true => spammeur
            if ( 'true' === $response[1] ) {
                return false;
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
