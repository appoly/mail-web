<?php

namespace Appoly\MailWeb\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MailwebEmailAttachment extends Model
{
    use HasUuids;

    protected $fillable = ['mailweb_email_id', 'name', 'path'];

    public function mailwebEmail()
    {
        return $this->belongsTo(MailwebEmail::class);
    }
}
