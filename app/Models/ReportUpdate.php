<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportUpdate extends Model
{
    protected $fillable = ['report_id', 'user_id', 'status', 'note', 'photo'];

    protected $casts = [
        'status' => ReportStatus::class,
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? ImageService::url($this->photo) : null;
    }

    public function getThumbUrlAttribute(): ?string
    {
        return $this->photo ? ImageService::url(ImageService::thumbPath($this->photo)) : null;
    }
}
