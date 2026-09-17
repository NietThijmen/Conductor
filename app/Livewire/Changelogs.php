<?php

namespace App\Livewire;

use App\Enums\ChangelogStatus;
use App\Jobs\GenerateChangelog;
use App\Models\Changelog;
use App\Models\Package;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Changelogs')]
#[Layout('layouts.app')]
class Changelogs extends Component
{
    public ?int $editingChangelogId = null;

    public ?int $package_id = null;

    public string $old_version = '';

    public string $new_version = '';

    public string $status = 'backlog';

    public ?int $deletingChangelogId = null;

    public function create(): void
    {
        $this->resetForm();
        $this->editingChangelogId = null;
    }

    public function edit(Changelog $changelog): void
    {
        $this->resetForm();
        $this->editingChangelogId = $changelog->id;
        $this->package_id = $changelog->package_id;
        $this->old_version = $changelog->old_version;
        $this->new_version = $changelog->new_version;
        $this->status = $changelog->status->value;
    }

    public function save(): void
    {
        $this->validate();

        GenerateChangelog::dispatch(
            package: Package::findOrFail($this->package_id),
            oldVersion: $this->old_version,
            newVersion: $this->new_version,
        );

        $this->resetForm();
        $this->editingChangelogId = null;
    }

    public function confirmDelete(Changelog $changelog): void
    {
        $this->deletingChangelogId = $changelog->id;
    }

    public function delete(): void
    {
        Changelog::findOrFail($this->deletingChangelogId)->delete();
        Flux::toast(variant: 'success', text: __('Changelog deleted.'));
        $this->deletingChangelogId = null;
    }

    public function rules(): array
    {
        return [
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'old_version' => ['required', 'string', 'max:255'],
            'new_version' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:backlog,checking,checked'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset(['editingChangelogId', 'package_id', 'old_version', 'new_version', 'status']);
        $this->status = 'backlog';
        $this->package_id = Package::query()->first()->id;
        $this->resetErrorBag();
    }

    #[Computed]
    public function backlog()
    {
        return Changelog::query()
            ->with('package')
            ->where('status', ChangelogStatus::Backlog)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function checking()
    {
        return Changelog::query()
            ->with('package')
            ->where('status', ChangelogStatus::Checking)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function checked()
    {
        return Changelog::query()
            ->with('package')
            ->where('status', ChangelogStatus::Checked)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function packages()
    {
        return Package::query()
            ->orderBy('name')
            ->get();
    }

    public function mount(): void
    {
        $this->package_id = Package::query()->first()->id;
        $this->status = ChangelogStatus::Backlog->value;
    }

    public function render()
    {
        return view('livewire.changelogs');
    }
}
