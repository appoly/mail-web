<?php

namespace Appoly\MailWeb\Http\Controllers;

use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Http\Request;

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
}
