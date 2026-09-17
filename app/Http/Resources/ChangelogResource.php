<?php

namespace App\Http\Resources;

use App\Models\Changelog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Changelog
 */
class ChangelogResource extends JsonResource
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
            'package' => $this->whenLoaded('package', fn () => new PackageResource($this->package)),
            'old_version' => $this->old_version,
            'new_version' => $this->new_version,
            'status' => $this->status->value,
            'title' => $this->title,
            'summary' => $this->summary,
            'changes' => $this->whenLoaded('changes', fn () => ChangelogChangeResource::collection($this->changes)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
