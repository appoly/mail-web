<?php

namespace Appoly\MailWeb\Http\Controllers;

use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Support\Facades\Gate;

class MailWebController
{
    public function index()
    {
        // $emails = MailwebEmail::get();

        if (Gate::denies('view-mailweb', auth()->user())) {
            return abort('403');
        }

        return view('mail-web::mail-web.index');
    }

    public function get()
    {
        $emails = MailwebEmail::get();

        return response()
            ->json($emails, 200);
    }
}
