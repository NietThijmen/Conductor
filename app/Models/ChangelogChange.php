<?php

namespace App\Models;

use App\Enums\ChangelogChangeType;
use Database\Factories\ChangelogChangeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $changelog_id
 * @property ChangelogChangeType $type
 * @property string $title
 * @property string|null $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['changelog_id', 'type', 'title', 'message'])]
class ChangelogChange extends Model
{
    /** @use HasFactory<ChangelogChangeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ChangelogChangeType::class,
        ];
    }

    /**
     * Get the changelog this change belongs to.
     *
     * @return BelongsTo<Changelog, $this>
     */
    public function changelog(): BelongsTo
    {
        return $this->belongsTo(Changelog::class);
    }
}
