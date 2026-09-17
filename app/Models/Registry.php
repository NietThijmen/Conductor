<?php

namespace App\Models;

use App\Enums\RegistryAuthType;
use Database\Factories\RegistryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $url
 * @property RegistryAuthType $auth_type
 * @property array<string, mixed>|null $auth_config
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'url', 'auth_type', 'auth_config', 'is_active'])]
class Registry extends Model
{
    /** @use HasFactory<RegistryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auth_type' => RegistryAuthType::class,
            'auth_config' => 'encrypted:array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the packages that belong to this registry.
     *
     * @return HasMany<Package, $this>
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }
}
