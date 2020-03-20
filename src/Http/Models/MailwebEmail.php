<?php

namespace Appoly\MailWeb\Http\Models;

use Illuminate\Database\Eloquent\Model;

class MailwebEmail extends Model
{
    protected $guarded = ['id'];

    protected $appends = [
        'body',
        'from_email',
        'to_email',
        'subject',
    ];

    protected $dates = [
        'created_at',
    ];

    public function getEmailAttribute($value)
    {
        return unserialize($value);
    }

    public function getBodyAttribute()
    {
        return $this->email->getBody();
    }

    public function getFromEmailAttribute()
    {
        return array_key_first($this->email->getFrom());
    }

    public function getToEmailAttribute()
    {
        return array_key_first($this->email->getTo());
    }

    public function getSubjectAttribute()
    {
        return $this->email->getSubject();
    }
}
