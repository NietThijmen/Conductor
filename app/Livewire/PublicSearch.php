<?php

namespace App\Livewire;

use App\Enums\ChangelogChangeType;
use App\Models\Changelog;
use App\Models\Package;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Find changelogs')]
#[Layout('layouts.public')]
class PublicSearch extends Component
{
    use WithPagination;

    /**
     * Search query synced with the URL query string.
     */
    #[Url(as: 'q', history: true)]
    public string $search = '';

    /**
     * Optional vendor filter synced with the URL.
     */
    #[Url(history: true)]
    public ?string $vendor = null;

    /**
     * Clear all active filters and reset pagination.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->vendor = null;
        $this->resetPage();
    }

    /**
     * Select a vendor filter.
     */
    public function filterByVendor(string $vendor): void
    {
        $this->vendor = $this->vendor === $vendor ? null : $vendor;
        $this->resetPage();
    }

    /**
     * Get the distinct vendors from active package names.
     *
     * @return Collection<int, Package>
     */
    #[Computed]
    public function vendors(): Collection
    {
        return Package::query()
            ->selectRaw("SUBSTR(name, 1, INSTR(name, '/') - 1) as vendor, COUNT(*) as count")
            ->where('is_active', true)
            ->whereNotNull('name')
            ->where('name', 'like', '%/%')
            ->groupBy('vendor')
            ->orderBy('vendor')
            ->get();
    }

    /**
     * Get the paginated list of packages matching the current filters.
     *
     * @return LengthAwarePaginator<int, Package>
     */
    #[Computed]
    public function packages(): LengthAwarePaginator
    {
        return Package::query()
            ->withCount('changelogs')
            ->with(['changelogs' => function ($query): void {
                $query->where('status', 'checked')
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
                    ->with('changes');
            }])
            ->where('is_active', true)
            ->when($this->vendor, function ($query): void {
                $escaped = str_replace('\\', '\\\\', $this->vendor);
                $query->where('name', 'like', $escaped.'/%');
            })
            ->when($this->search, function ($query): void {
                $term = '%'.str_replace('\\', '\\\\', $this->search).'%';
                $query->where('name', 'like', $term);
            })
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * Get a summary map of change counts for a changelog.
     *
     * @return array<string, int>
     */
    public function changeSummary(Changelog $changelog): array
    {
        return [
            'breaking' => $changelog->changes->where('type', ChangelogChangeType::Breaking)->count(),
            'new' => $changelog->changes->where('type', ChangelogChangeType::New)->count(),
            'updated' => $changelog->changes->where('type', ChangelogChangeType::Updated)->count(),
        ];
    }

    public function render(): View
    {
        $title = 'Find changelogs';

        if ($this->search) {
            $title = "Search: {$this->search}";
        } elseif ($this->vendor) {
            $title = "Packages by {$this->vendor}";
        }

        return view('livewire.public-search')->title($title);
    }
}
