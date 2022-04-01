<?php

namespace Appoly\MailWeb\Http\Controllers;

use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MailWebController
{
    public function index()
    {
        if (Gate::denies('view-mailweb', auth()->user())) {
            return abort('403');
        }

        return view('mail-web::mail-web.index');
    }

    public function get(Request $request)
    {
        $emails = MailwebEmail::orderBy('created_at', 'DESC');
        if ($request->has('last_email_date') && $request->last_email_date !== null) {
            $emails->where('created_at', '>', $request->last_email_date);
        }
        $emails = $emails->get();

        return response()
            ->json($emails, 200);
    }
}
