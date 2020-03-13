<?php

namespace Appoly\MailWeb\Remote;

class Spam
{
    /**
     * Get spam score.
     *
     * @param [type] $email
     * @return void
     */
    public static function getSpamScore($email)
    {
        if (! $email) {
            return false;
        }

        $client = new Client([
            'base_uri' => 'https://spamcheck.postmarkapp.com
',
        ]);

        $response = $client->request('POST', 'filter', [
            'body' => [
                'email' => $email->email->toString()
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
