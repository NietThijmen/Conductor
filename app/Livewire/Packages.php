<?php

namespace App\Livewire;

use App\Models\Package;
use App\Models\Registry;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Packages')]
#[Layout('layouts.app')]
class Packages extends Component
{
    use WithPagination;

    public ?int $editingPackageId = null;

    public string $name = '';

    public ?string $registry_id = null;

    public ?string $current_version = null;

    public bool $is_active = true;

    public ?int $deletingPackageId = null;

    public function create(): void
    {
        $this->resetForm();
        $this->editingPackageId = null;
    }

    public function edit(Package $package): void
    {
        $this->resetForm();
        $this->editingPackageId = $package->id;
        $this->name = $package->name;
        $this->registry_id = $package->registry_id;
        $this->current_version = $package->current_version;
        $this->is_active = $package->is_active;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'registry_id' => $this->registry_id,
            'current_version' => $this->current_version,
            'is_active' => $this->is_active,
        ];

        if ($this->editingPackageId) {
            Package::findOrFail($this->editingPackageId)->update($data);
            Flux::toast(variant: 'success', text: __('Package updated.'));
        } else {
            Package::create($data);
            Flux::toast(variant: 'success', text: __('Package created.'));
        }

        $this->resetForm();
        $this->editingPackageId = null;
    }

    public function confirmDelete(Package $package): void
    {
        $this->deletingPackageId = $package->id;
    }

    public function delete(): void
    {
        Package::findOrFail($this->deletingPackageId)->delete();
        Flux::toast(variant: 'success', text: __('Package deleted.'));
        $this->deletingPackageId = null;
    }

    public function toggleActive(Package $package): void
    {
        $package->update(['is_active' => ! $package->is_active]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255', Rule::unique('packages', 'name')->ignore($this->editingPackageId)],
            'registry_id' => ['nullable', 'integer', 'exists:registries,id'],
            'current_version' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'registry_id', 'current_version', 'is_active']);
        $this->is_active = true;
        $this->resetErrorBag();
    }

    #[Computed]
    public function packages()
    {
        return Package::query()
            ->with('registry')
            ->orderBy('name')
            ->paginate(15);
    }

    #[Computed]
    public function registries()
    {
        return Registry::query()
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.packages');
    }
}
