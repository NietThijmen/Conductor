<?php

namespace App\Livewire;

use App\Models\Registry;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Registries')]
#[Layout('layouts.app')]
class Registries extends Component
{
    use WithPagination;

    public ?int $editingRegistryId = null;

    public string $name = '';

    public string $url = '';

    public string $auth_type = 'none';

    public ?string $auth_config_username = null;

    public ?string $auth_config_password = null;

    public ?string $auth_config_token = null;

    public bool $is_active = true;

    public ?int $deletingRegistryId = null;

    public function create(): void
    {
        $this->resetForm();
        $this->editingRegistryId = null;
    }

    public function edit(Registry $registry): void
    {
        $this->resetForm();
        $this->editingRegistryId = $registry->id;
        $this->name = $registry->name;
        $this->url = $registry->url;
        $this->auth_type = $registry->auth_type->value;
        $this->is_active = $registry->is_active;

        if ($registry->auth_config) {
            $this->auth_config_username = $registry->auth_config['username'] ?? null;
            $this->auth_config_password = $registry->auth_config['password'] ?? null;
            $this->auth_config_token = $registry->auth_config['token'] ?? null;
        }
    }

    public function save(): void
    {
        $this->validate();

        $authConfig = match ($this->auth_type) {
            'basic' => ['username' => $this->auth_config_username, 'password' => $this->auth_config_password],
            'token' => ['token' => $this->auth_config_token],
            'composer' => ['username' => $this->auth_config_username, 'password' => $this->auth_config_password],
            default => null,
        };

        $data = [
            'name' => $this->name,
            'url' => $this->url,
            'auth_type' => $this->auth_type,
            'auth_config' => $authConfig,
            'is_active' => $this->is_active,
        ];

        if ($this->editingRegistryId) {
            Registry::findOrFail($this->editingRegistryId)->update($data);
            Flux::toast(variant: 'success', text: __('Registry updated.'));
        } else {
            Registry::create($data);
            Flux::toast(variant: 'success', text: __('Registry created.'));
        }

        $this->resetForm();
        $this->editingRegistryId = null;
    }

    public function confirmDelete(Registry $registry): void
    {
        $this->deletingRegistryId = $registry->id;
    }

    public function delete(): void
    {
        Registry::findOrFail($this->deletingRegistryId)->delete();
        Flux::toast(variant: 'success', text: __('Registry deleted.'));
        $this->deletingRegistryId = null;
    }

    public function toggleActive(Registry $registry): void
    {
        $registry->update(['is_active' => ! $registry->is_active]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255', Rule::unique('registries', 'url')->ignore($this->editingRegistryId)],
            'auth_type' => ['required', 'in:none,basic,token,composer'],
            'auth_config_username' => ['nullable', 'string', 'max:255'],
            'auth_config_password' => ['nullable', 'string', 'max:255'],
            'auth_config_token' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function resetForm(): void
    {
        $this->reset([
            'name', 'url', 'auth_type', 'auth_config_username',
            'auth_config_password', 'auth_config_token', 'is_active',
        ]);
        $this->is_active = true;
        $this->auth_type = 'none';
        $this->resetErrorBag();
    }

    /**
     * @return LengthAwarePaginator<int, Registry>
     */
    #[Computed]
    public function registries(): LengthAwarePaginator
    {
        return Registry::query()
            ->orderBy('name')
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.registries');
    }
}
