<?php

namespace Appoly\MailWeb\Http\Controllers;

use Appoly\MailWeb\Http\Models\MailwebEmailAttachment;
use Illuminate\Support\Facades\Gate;
use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Support\Facades\Storage;

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

    public function getAttachment(MailwebEmail $mailwebEmail, MailwebEmailAttachment $attachment)
    {
        if (Gate::denies('view-mailweb', auth()->user())) {
            abort(403);
        }

        try {
            $storageDisk = config('MailWeb.MAILWEB_ATTACHMENTS.DISK');

            if (!$storageDisk) {
                throw new \Exception('Storage disk not configured');
            }

            return Storage::disk($storageDisk)->download($attachment->path, $attachment->name);
        } catch (\Throwable $th) {
            report($th);
            return view('mailweb::error', [
                'errorMessage' => $th->getMessage(),
            ]);
        }
    }
}
