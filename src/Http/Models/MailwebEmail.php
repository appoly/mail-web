<?php

namespace Appoly\MailWeb\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MailwebEmail extends Model
{
    protected $guarded = ['id'];

    protected $dates = [
        'created_at',
    ];

    protected $casts = [
        'from' => 'json',
        'to' => 'json',
        'cc' => 'json',
        'bcc' => 'json',
    ];

    // public function getAttachmentsAttribute()
    // {
    //     $attachments = [];
    //     $emailAttachments = $this->email->getAttachments();
    //     foreach ($emailAttachments as $attachment) {
    //         $attachments[] = [
    //             'name' => $attachment->getFilename(),
    //             // 'content' => $attachment->getBody()
    //         ];
    //     }
    //     return $attachments;
    // }

    // public function scopeFilterByDates($query, $start, $end)
    // {
    //     return $query->when($start, function ($query, $start) {
    //         return $query->whereDate('created_at', '>=', $start);
    //     })->when($end, function ($query, $end) {
    //         return $query->whereDate('created_at', '<=', $end);
    //     });
    // }

    public function getSnippetAttribute()
    {
        // truncate $this->body_text to 100 characters with ...
        return Str::limit($this->body_text, 130, '...');
    }
}
