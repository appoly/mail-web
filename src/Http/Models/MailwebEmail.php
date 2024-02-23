<?php

namespace Appoly\MailWeb\Http\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MailwebEmail extends Model
{
    use HasUuids;

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

    public function scopeSearch($query, $search)
    {
        return $query->where('subject', 'like', "%$search%")
            ->orWhere('body_text', 'like', "%$search%")
            ->orWhere('body_html', 'like', "%$search%");
    }

    public function getSnippetAttribute()
    {
        // truncate $this->body_text to 100 characters with ...
        return Str::limit($this->body_text, 130, '...');
    }
}
