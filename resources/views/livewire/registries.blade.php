<div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Registries') }}</flux:heading>
                <flux:text>{{ __('Manage your Composer package registries.') }}</flux:text>
            </div>

            <flux:modal.trigger name="create-registry">
                <flux:button wire:click="create" variant="primary">{{ __('Add registry') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <flux:table :paginate="$this->registries">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('URL') }}</flux:table.column>
                <flux:table.column>{{ __('Authentication') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column />
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->registries as $registry)
                    <flux:table.row :key="$registry->id">
                        <flux:table.cell class="font-medium">{{ $registry->name }}</flux:table.cell>
                        <flux:table.cell class="max-w-64 truncate">{{ $registry->url }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$registry->auth_type->value === 'none' ? 'zinc' : 'blue'">
                                {{ $registry->auth_type->name }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge
                                size="sm"
                                :color="$registry->is_active ? 'green' : 'zinc'"
                                class="cursor-pointer select-none"
                                wire:click="toggleActive({{ $registry->id }})"
                            >
                                {{ $registry->is_active ? __('Active') : __('Inactive') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="py-0">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="left"></flux:button>

                                <flux:menu>
                                    <flux:modal.trigger name="create-registry">
                                        <flux:menu.item wire:click="edit({{ $registry->id }})" icon="pencil">
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                    </flux:modal.trigger>

                                    <flux:modal.trigger name="delete-registry">
                                        <flux:menu.item wire:click="confirmDelete({{ $registry->id }})" icon="trash" variant="danger">
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

        <flux:modal name="create-registry" class="md:w-[500px]">
            <form wire:submit="save" class="space-y-6">
                <div>
                    <flux:heading size="lg">
                        {{ $editingRegistryId ? __('Edit registry') : __('Create registry') }}
                    </flux:heading>
                    <flux:text class="mt-2">
                        {{ $editingRegistryId ? __('Update the registry details below.') : __('Add a new Composer package registry.') }}
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label>{{ __('Name') }}</flux:label>
                    <flux:input wire:model="name" placeholder="e.g. Private Packagist" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('URL') }}</flux:label>
                    <flux:input wire:model="url" type="url" placeholder="https://repo.packagist.com/example/" />
                    <flux:error name="url" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Authentication type') }}</flux:label>
                    <flux:select wire:model.live="auth_type">
                        <option value="none">{{ __('None') }}</option>
                        <option value="basic">{{ __('HTTP Basic') }}</option>
                        <option value="token">{{ __('Token / Bearer') }}</option>
                        <option value="composer">{{ __('Composer') }}</option>
                    </flux:select>
                    <flux:error name="auth_type" />
                </flux:field>

                @if (in_array($auth_type, ['basic', 'composer']))
                    <flux:field>
                        <flux:label>{{ __('Username') }}</flux:label>
                        <flux:input wire:model="auth_config_username" />
                        <flux:error name="auth_config_username" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Password') }}</flux:label>
                        <flux:input wire:model="auth_config_password" type="password" />
                        <flux:error name="auth_config_password" />
                    </flux:field>
                @endif

                @if ($auth_type === 'token')
                    <flux:field>
                        <flux:label>{{ __('Token') }}</flux:label>
                        <flux:input wire:model="auth_config_token" type="password" />
                        <flux:error name="auth_config_token" />
                    </flux:field>
                @endif

                <flux:field>
                    <flux:label>{{ __('Active') }}</flux:label>
                    <flux:switch wire:model="is_active" />
                    <flux:error name="is_active" />
                </flux:field>

                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">
                        {{ $editingRegistryId ? __('Update registry') : __('Create registry') }}
                    </flux:button>
                </div>
            </form>
        </flux:modal>

        <flux:modal name="delete-registry" class="md:w-96">
            <form wire:submit="delete" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete registry') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __('Are you sure you want to delete this registry? This action cannot be undone.') }}
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
</div>