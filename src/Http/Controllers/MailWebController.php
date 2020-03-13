<?php

namespace Appoly\MailWeb\Http\Controllers;

use Appoly\MailWeb\Http\Models\MailwebEmail;
use Appoly\MailWeb\Remote\Spam;

class MailWebController
{
    public function index()
    {
        // $emails = MailwebEmail::get();

        return view('mail-web::mail-web.index');
    }

    public function get()
    {
        $emails = MailwebEmail::get();

        return response()
            ->json($emails, 200);
    }

    public function spamScore(MailwebEmail $email)
    {
        $spamScore = Spam::getSpamScore($email);

        return response()
            ->json($spamScore, 200);
    }
}
