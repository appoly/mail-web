<?php

namespace Appoly\MailWeb\Observers;

use Appoly\MailWeb\Http\Models\MailwebEmail;
use Illuminate\Support\Facades\Storage;

class MailwebEmailObserver
{
    public function deleting(MailwebEmail $email)
    {
        foreach ($email->attachments as $attachment) {
            if ($attachment->path && Storage::exists($attachment->path)) {
                Storage::delete($attachment->path);
            }
            $attachment->delete();
        }
    }
}