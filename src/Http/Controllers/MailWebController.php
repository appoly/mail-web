<?php

namespace Appoly\MailWeb\Http\Controllers;

use Appoly\MailWeb\Http\Models\MailwebEmail;

class MailWebController
{
    public function index()
    {
        return view('mailweb::dashboard');
    }

    public function show(MailwebEmail $mailwebEmail)
    {
        abort_if(! $mailwebEmail->share_enabled, 403);

        return view('mailweb::email', [
            'email' => $mailwebEmail,
        ]);
    }
}
