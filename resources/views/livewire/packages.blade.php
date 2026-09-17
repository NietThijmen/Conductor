<div class="flex w-full flex-1 flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Packages') }}</flux:heading>
            <flux:text>{{ __('Manage your Composer packages.') }}</flux:text>
        </div>

        <flux:modal.trigger name="create-package">
            <flux:button wire:click="create" variant="primary">{{ __('Add package') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table :paginate="$this->packages">
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Registry') }}</flux:table.column>
            <flux:table.column>{{ __('Current version') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column />
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->packages as $package)
                <flux:table.row :key="$package->id">
                    <flux:table.cell class="font-medium">{{ $package->name }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($package->registry)
                            <flux:badge size="sm" color="zinc">{{ $package->registry->name }}</flux:badge>
                        @else
                            <flux:text class="italic text-zinc-400">{{ __('None') }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($package->current_version)
                            <code class="text-sm">{{ $package->current_version }}</code>
                        @else
                            <flux:text class="italic text-zinc-400">{{ __('Unknown') }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge
                            size="sm"
                            :color="$package->is_active ? 'green' : 'zinc'"
                            class="cursor-pointer select-none"
                            wire:click="toggleActive({{ $package->id }})"
                        >
                            {{ $package->is_active ? __('Active') : __('Inactive') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="py-0">
                        <flux:dropdown position="bottom" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="left"></flux:button>

                            <flux:menu>
                                <flux:modal.trigger name="create-package">
                                    <flux:menu.item wire:click="edit({{ $package->id }})" icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                </flux:modal.trigger>

                                <flux:modal.trigger name="delete-package">
                                    <flux:menu.item wire:click="confirmDelete({{ $package->id }})" icon="trash" variant="danger">
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:modal.trigger>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="create-package" class="md:w-[500px]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingPackageId ? __('Edit package') : __('Create package') }}
                </flux:heading>
                <flux:text class="mt-2">
                    {{ $editingPackageId ? __('Update the package details below.') : __('Add a new Composer package.') }}
                </flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="name" placeholder="vendor/package-name" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Registry') }}</flux:label>
                <flux:select wire:model="registry_id" placeholder="{{ __('No registry') }}">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($this->registries as $registry)
                        <option value="{{ $registry->id }}">{{ $registry->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="registry_id" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Current version') }}</flux:label>
                <flux:input wire:model="current_version" placeholder="e.g. 1.0.0" />
                <flux:error name="current_version" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Active') }}</flux:label>
                <flux:switch wire:model="is_active" />
                <flux:error name="is_active" />
            </flux:field>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">
                    {{ $editingPackageId ? __('Update package') : __('Create package') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-package" class="md:w-96">
        <form wire:submit="delete" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete package') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Are you sure you want to delete this package? This action cannot be undone.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>