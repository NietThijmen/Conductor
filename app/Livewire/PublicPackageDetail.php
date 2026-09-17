<?php

namespace App\Livewire;

use App\Models\Changelog;
use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('{package.name}')]
#[Layout('layouts.public')]
class PublicPackageDetail extends Component
{
    public Package $package;

    public ?string $vendor = null;

    public ?string $name = null;

    public function mount(?string $vendor = null, ?string $name = null): void
    {
        $this->vendor = $vendor;
        $this->name = $name;

        $fullName = trim(implode('/', array_filter([$vendor, $name])), '/');

        $this->package = Package::query()
            ->where('is_active', true)
            ->where('name', $fullName)
            ->firstOrFail();
    }

    /**
     * Get the checked changelogs for this package, newest first.
     *
     * @return Collection<int, Changelog>
     */
    #[Computed]
    public function changelogs(): Collection
    {
        return $this->package
            ->changelogs()
            ->where('status', 'checked')
            ->orderBy('created_at', 'desc')
            ->with('changes')
            ->get();
    }

    public function render()
    {
        return view('livewire.public-package-detail')
            ->title("{$this->package->name} changelog");
    }
}
