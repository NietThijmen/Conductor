<?php

namespace App\Livewire;

use App\Models\Changelog;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('{changelog.title}')]
#[Layout('layouts.public')]
class PublicChangelogDetail extends Component
{
    public Changelog $changelog;

    public function mount(string $vendor, string $name, string $new_version): void
    {
        $packageName = trim(implode('/', [$vendor, $name]), '/');
        $this->changelog = Changelog::query()
            ->whereHas('package', function ($query) use ($packageName): void {
                $query->where('is_active', true)->where('name', $packageName);
            })
            ->where('status', 'checked')
            ->where('new_version', $new_version)
            ->with(['package', 'changes'])
            ->firstOrFail();
    }

    public function render(): View
    {
        $title = $this->changelog->title
            ?? "{$this->changelog->package->name} {$this->changelog->new_version} changelog";

        return view('livewire.public-changelog-detail')->title($title);
    }
}
