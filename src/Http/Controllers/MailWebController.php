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
        $emails = MailwebEmail::orderBy('created_at', 'DESC')
            ->filterByDates($request->from, $request->to)
            ->get()
            ->map(function ($email) {
                return [
                    'id' => $email->id,
                    'body' => $email->body,
                    'from_emails' => $email->from_email,
                    'to_emails' => $email->to_emails,
                    'subject' => $email->subject,
                    'attachments' => $email->attachments,
                    'created_at' => $email->created_at,
                ];
            });

        return response()
            ->json($emails, 200);
    }
}
