<?php

namespace Appoly\MailWeb\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailwebEmail extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected $appends = [
        'share_url',
    ];

    protected $casts = [
        'from' => 'json',
        'to' => 'json',
        'cc' => 'json',
        'bcc' => 'json',
        'created_at' => 'datetime',
    ];

    /**
     * @return HasMany<MailwebEmailAttachment>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MailwebEmailAttachment::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeSearch($query, string $search): void
    {
        $query->where('subject', 'like', "%{$search}%")
            ->orWhere('body_text', 'like', "%{$search}%")
            ->orWhere('body_html', 'like', "%{$search}%");
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeShareEnabled($query): void
    {
        $query->where('share_enabled', true);
    }

    public function getAttachmentCountAttribute(): int
    {
        return $this->attachments_count ?? $this->attachments()->count();
    }

    public function getShareUrlAttribute(): ?string
    {
        return $this->share_enabled ? route('mailweb.share', $this) : null;
    }
}
