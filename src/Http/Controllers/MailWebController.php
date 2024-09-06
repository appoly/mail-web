<?php

namespace Appoly\MailWeb\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Appoly\MailWeb\Http\Models\MailwebEmail;

class MailWebController
{
    public function index()
    {
        if (Gate::denies('view-mailweb', auth()->user())) {
            abort(403);
        }

        return view('mailweb::dashboard');
    }

    public function show(MailwebEmail $mailwebEmail)
    {

        if (Gate::denies('view-mailweb', auth()->user())) {
            abort(403);
        }

        abort_if(! $mailwebEmail->share_enabled, 403);

        return view('mailweb::email', [
            'email' => $mailwebEmail,
        ]);
    }
}
