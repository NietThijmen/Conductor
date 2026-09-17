<?php

namespace App\Http\Resources;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Package
 */
class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'current_version' => $this->current_version,
            'is_active' => $this->is_active,
            'changelogs_count' => $this->whenCounted('changelogs'),
            'latest_changelog' => $this->whenLoaded('latestChangelog', fn () => new ChangelogResource($this->latestChangelog)),
            'changelogs' => $this->whenLoaded('changelogs', fn () => ChangelogResource::collection($this->changelogs)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
