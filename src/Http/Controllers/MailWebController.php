<?php

namespace Appoly\MailWeb\Http\Controllers;

use Appoly\MailWeb\Http\Models\MailwebEmail;
use Appoly\MailWeb\Http\Requests\MailWebRequest;
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

    public function get(MailWebRequest $request)
    {
        $emails = MailwebEmail::orderBy('created_at', 'DESC')
            ->applyFilters($request->validated())
            ->cursorPaginate(config('mail-web.MAILWEB_PER_PAGE'))
            ->through(function ($email) {
                try {
                    return [
                        'id' => $email->id,
                        'body' => mb_convert_encoding($email->body, 'UTF-8'), // Convert to UTF-8 to prevent JSON errors
                        'from_email' => $email->from_email,
                        'to_emails' => $email->to_emails,
                        'subject' => $email->subject,
                        'attachments' => $email->attachments,
                        'created_at' => $email->created_at,
                    ];
                } catch (\Throwable $th) {
                    return [
                        'error' => true,
                        'id' => $email->id,
                        'body' => '',
                        'from_email' => '',
                        'to_emails' => '',
                        'subject' => '',
                        'attachments' => '',
                        'created_at' => $email->created_at, // This one should never throw an exception
                    ];
                }
            });

        return response()
            ->json($emails, 200);
    }

    public function delete(MailwebEmail $mailwebEmail) {
        $mailwebEmail->delete();
        return response()->json(['success' => true]);
    }
}
