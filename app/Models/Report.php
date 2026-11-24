<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Services\ImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory;

    /** Dukungan mencapai angka ini atau lebih = "Prioritas". */
    public const PRIORITY_THRESHOLD = 10;

    protected $fillable = [
        'tracking_code', 'user_id', 'category_id', 'region_id',
        'title', 'description', 'address', 'latitude', 'longitude', 'photo',
        'status', 'upvotes_count',
        'assigned_to', 'verified_by', 'verified_at', 'rejection_reason', 'completed_at',
    ];

    protected $casts = [
        'status' => ReportStatus::class,
        'latitude' => 'float',
        'longitude' => 'float',
        'upvotes_count' => 'integer',
        'verified_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Report $report) {
            if (! $report->tracking_code) {
                $report->tracking_code = static::generateTrackingCode();
            }
        });
    }

    /** URL cantik: /laporan/LG-2611-AB12C */
    public function getRouteKeyName(): string
    {
        return 'tracking_code';
    }

    public static function generateTrackingCode(): string
    {
        do {
            $code = 'LG-' . now()->format('ym') . '-' . strtoupper(Str::random(5));
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }

    /* ----------------------------------------------------------------
     |  Relasi
     * ---------------------------------------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReportVote::class);
    }

    public function voters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'report_votes')->withTimestamps();
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ReportUpdate::class)->orderBy('created_at')->orderBy('id');
    }

    /* ----------------------------------------------------------------
     |  Query Scopes
     * ---------------------------------------------------------------- */

    /** Laporan yang boleh dilihat publik (bukan yang ditolak/spam). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', '!=', ReportStatus::Rejected->value);
    }

    /** Laporan yang belum selesai ditangani. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ReportStatus::activeValues());
    }

    public function scopeStatus(Builder $query, string|ReportStatus $status): Builder
    {
        return $query->where('status', $status instanceof ReportStatus ? $status->value : $status);
    }

    /** Prioritas utama: dukungan warga terbanyak, lalu yang terbaru. */
    public function scopeMostUpvoted(Builder $query): Builder
    {
        return $query->orderByDesc('upvotes_count')->orderByDesc('created_at');
    }

    public function scopeOfCategory(Builder $query, ?string $slug): Builder
    {
        return $query->when($slug, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)));
    }

    public function scopeInRegion(Builder $query, int|string|null $regionId): Builder
    {
        return $query->when($regionId, fn ($q) => $q->where('region_id', $regionId));
    }

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->where('assigned_to', $user->id);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function ($q) use ($term) {
            $like = '%' . trim($term) . '%';
            $q->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)
                    ->orWhere('address', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('tracking_code', 'like', $like);
            });
        });
    }

    /** Gabungan filter dari query string: q, category, region, status. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->search($filters['q'] ?? null)
            ->ofCategory($filters['category'] ?? null)
            ->inRegion($filters['region'] ?? null)
            ->when($filters['status'] ?? null, function ($q, $status) {
                if (ReportStatus::tryFrom($status)) {
                    $q->status($status);
                }
            });
    }

    public function scopeSortedBy(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'terbaru' => $query->latest(),
            'terlama' => $query->oldest(),
            default => $query->mostUpvoted(),
        };
    }

    /** Tandai apakah user tertentu sudah memberi dukungan (tanpa N+1). */
    public function scopeWithVotedBy(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query;
        }

        return $query->withExists(['votes as voted_by_me' => fn ($v) => $v->where('user_id', $user->id)]);
    }

    /* ----------------------------------------------------------------
     |  Voting
     * ---------------------------------------------------------------- */

    public function hasVotedBy(?User $user): bool
    {
        return $user ? $this->votes()->where('user_id', $user->id)->exists() : false;
    }

    /**
     * Beri / cabut dukungan. Mengembalikan true bila sekarang berstatus "didukung".
     * Counter di-sinkronkan dari tabel report_votes agar selalu akurat.
     */
    public function toggleVote(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $vote = $this->votes()->firstOrCreate(['user_id' => $user->id]);
            $voted = $vote->wasRecentlyCreated;

            if (! $voted) {
                $vote->delete();
            }

            $count = $this->votes()->count();
            static::whereKey($this->getKey())->update(['upvotes_count' => $count]);
            $this->setAttribute('upvotes_count', $count);
            $this->syncOriginalAttribute('upvotes_count');

            return $voted;
        });
    }

    /* ----------------------------------------------------------------
     |  Accessor
     * ---------------------------------------------------------------- */

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? ImageService::url($this->photo) : null;
    }

    public function getThumbUrlAttribute(): ?string
    {
        return $this->photo ? ImageService::url(ImageService::thumbPath($this->photo)) : null;
    }

    /** Skala prioritas otomatis: dukungan >= 10 = prioritas. Selalu mengikuti upvotes_count terbaru. */
    public function getIsPriorityAttribute(): bool
    {
        return (int) $this->upvotes_count >= self::PRIORITY_THRESHOLD;
    }

    public function getPriorityLabelAttribute(): string
    {
        return $this->is_priority ? 'Prioritas' : 'Tidak Diprioritaskan';
    }

    /** Foto bukti penyelesaian dari petugas (update "done" terakhir). */
    public function getProofPhotoUrlAttribute(): ?string
    {
        $proof = $this->relationLoaded('updates')
            ? $this->updates->where('status', ReportStatus::Done)->whereNotNull('photo')->last()
            : $this->updates()->where('status', ReportStatus::Done->value)->whereNotNull('photo')->latest('id')->first();

        return $proof?->photo_url;
    }

    /** Foto bukti lapangan dari petugas saat laporan ditolak (update "rejected" terakhir). */
    public function getRejectionPhotoUrlAttribute(): ?string
    {
        $proof = $this->relationLoaded('updates')
            ? $this->updates->where('status', ReportStatus::Rejected)->whereNotNull('photo')->last()
            : $this->updates()->where('status', ReportStatus::Rejected->value)->whereNotNull('photo')->latest('id')->first();

        return $proof?->photo_url;
    }
}