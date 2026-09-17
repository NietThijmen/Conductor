<?php

namespace App\Models;

use App\Enums\ChangelogStatus;
use Database\Factories\ChangelogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $package_id
 * @property string $old_version
 * @property string $new_version
 * @property ChangelogStatus $status
 * @property string|null $title
 * @property string|null $summary
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['package_id', 'old_version', 'new_version', 'status', 'title', 'summary'])]
class Changelog extends Model
{
    /** @use HasFactory<ChangelogFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ChangelogStatus::class,
        ];
    }

    /**
     * Get the package this changelog belongs to.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the individual changes within this changelog.
     */
    public function changes(): HasMany
    {
        return $this->hasMany(ChangelogChange::class);
    }
}
