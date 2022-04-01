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
        'subject'
    ];

    protected $dates = [
        'created_at'
    ];

    public function getEmailAttribute($value)
    {
        return unserialize($value);
    }

    public function getBodyAttribute()
    {
        return $this->email->getBody()->getParts()[1]->getBody();
    }

    public function getFromEmailAttribute()
    {
        return $this->email->getFrom()[0]->getAddress();
    }

    public function getToEmailAttribute()
    {
        return $this->email->getTo()[0]->getAddress();
    }

    public function getSubjectAttribute()
    {
        return $this->email->getSubject();
    }
}
