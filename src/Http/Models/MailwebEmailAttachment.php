<?php

namespace Appoly\MailWeb\Http\Models;

use Illuminate\Support\Number;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MailwebEmailAttachment extends Model
{
    use HasUuids;

    protected $fillable = ['mailweb_email_id', 'name', 'path', 'size_bytes'];

    protected $appends = ['human_readable_size', 'file_url'];

    public function mailwebEmail()
    {
        return $this->belongsTo(MailwebEmail::class);
    }

    public function getHumanReadableSizeAttribute()
    {
        if ($this->size_bytes === null) {
            return 'Unknown';
        }

        return Number::fileSize($this->size_bytes);
    }

    public function getFileUrlAttribute()
    {
        if ($this->path === null) {
            return null;
        }

        $disk = Storage::disk(config('MailWeb.MAILWEB_ATTACHMENTS.DISK'));

        if (method_exists($disk, 'temporaryUrl')) {
            try {
                return $disk->temporaryUrl($this->path, now()->addMinutes(30));
            } catch (\RuntimeException) {
                // Driver doesn't support temporary URLs (e.g. local disk)
            }
        }

        return $disk->url($this->path);
    }
}
