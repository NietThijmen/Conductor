<div class="flex w-full flex-1 flex-col gap-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Changelogs') }}</flux:heading>
            <flux:text>{{ __('Review and manage package changelogs.') }}</flux:text>
        </div>

        <flux:modal.trigger name="create-changelog">
            <flux:button wire:click="create" variant="primary">{{ __('New changelog') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="min-h-0 flex-1 overflow-x-auto" wire:poll.visible>
        <flux:kanban>
            <flux:kanban.column>
                <flux:kanban.column.header heading="Backlog" :count="$this->backlog->count()">
                    <x-slot name="actions">
                        <flux:modal.trigger name="create-changelog">
                            <flux:button wire:click="create" variant="subtle" icon="plus" size="sm" />
                        </flux:modal.trigger>
                    </x-slot>
                </flux:kanban.column.header>

                <flux:kanban.column.cards>
                    @foreach ($this->backlog as $changelog)
                        <flux:kanban.card as="button" wire:click="edit({{ $changelog->id }})">
                            <x-slot name="header">
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" color="zinc">{{ $changelog->package->name }}</flux:badge>
                                    <flux:text class="text-xs tabular-nums text-zinc-400">
                                        {{ $changelog->old_version }} &rarr; {{ $changelog->new_version }}
                                    </flux:text>
                                </div>
                            </x-slot>

                            <flux:heading class="text-sm">{{ $changelog->title ?? $changelog->package->name.' changelog' }}</flux:heading>

                            @if ($changelog->summary)
                                <p class="mt-1 text-xs text-zinc-500 line-clamp-2">{{ $changelog->summary }}</p>
                            @endif

                            <x-slot name="footer">
                                <flux:text class="text-xs text-zinc-400">{{ $changelog->created_at->diffForHumans() }}</flux:text>
                            </x-slot>
                        </flux:kanban.card>
                    @endforeach
                </flux:kanban.column.cards>
            </flux:kanban.column>

            <flux:kanban.column>
                <flux:kanban.column.header heading="Checking" :count="$this->checking->count()">
                    <x-slot name="actions">
                        <flux:modal.trigger name="create-changelog">
                            <flux:button wire:click="create" variant="subtle" icon="plus" size="sm" />
                        </flux:modal.trigger>
                    </x-slot>
                </flux:kanban.column.header>

                <flux:kanban.column.cards>
                    @foreach ($this->checking as $changelog)
                        <flux:kanban.card as="button" wire:click="edit({{ $changelog->id }})">
                            <x-slot name="header">
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" color="zinc">{{ $changelog->package->name }}</flux:badge>
                                    <flux:text class="text-xs tabular-nums text-zinc-400">
                                        {{ $changelog->old_version }} &rarr; {{ $changelog->new_version }}
                                    </flux:text>
                                </div>
                            </x-slot>

                            <flux:heading class="text-sm">{{ $changelog->title ?? $changelog->package->name.' changelog' }}</flux:heading>

                            @if ($changelog->summary)
                                <p class="mt-1 text-xs text-zinc-500 line-clamp-2">{{ $changelog->summary }}</p>
                            @endif

                            <x-slot name="footer">
                                <flux:text class="text-xs text-zinc-400">{{ $changelog->created_at->diffForHumans() }}</flux:text>
                            </x-slot>
                        </flux:kanban.card>
                    @endforeach
                </flux:kanban.column.cards>
            </flux:kanban.column>

            <flux:kanban.column>
                <flux:kanban.column.header heading="Checked" :count="$this->checked->count()">
                    <x-slot name="actions">
                        <flux:modal.trigger name="create-changelog">
                            <flux:button wire:click="create" variant="subtle" icon="plus" size="sm" />
                        </flux:modal.trigger>
                    </x-slot>
                </flux:kanban.column.header>

                <flux:kanban.column.cards>
                    @foreach ($this->checked as $changelog)
                        <flux:kanban.card as="button" wire:click="edit({{ $changelog->id }})">
                            <x-slot name="header">
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" color="zinc">{{ $changelog->package->name }}</flux:badge>
                                    <flux:text class="text-xs tabular-nums text-zinc-400">
                                        {{ $changelog->old_version }} &rarr; {{ $changelog->new_version }}
                                    </flux:text>
                                </div>
                            </x-slot>

                            <flux:heading class="text-sm">{{ $changelog->title ?? $changelog->package->name.' changelog' }}</flux:heading>

                            @if ($changelog->summary)
                                <p class="mt-1 text-xs text-zinc-500 line-clamp-2">{{ $changelog->summary }}</p>
                            @endif

                            <x-slot name="footer">
                                <flux:text class="text-xs text-zinc-400">{{ $changelog->created_at->diffForHumans() }}</flux:text>
                            </x-slot>
                        </flux:kanban.card>
                    @endforeach
                </flux:kanban.column.cards>
            </flux:kanban.column>
        </flux:kanban>
    </div>

    <flux:modal name="create-changelog" class="md:w-[500px]">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingChangelogId ? __('Edit changelog') : __('Create changelog') }}
                </flux:heading>
                <flux:text class="mt-2">
                    {{ $editingChangelogId ? __('Update the changelog details below.') : __('Add a new package changelog.') }}
                </flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Package') }}</flux:label>
                <flux:select wire:model="package_id">
                    @foreach ($this->packages as $package)
                        <option value="{{ $package->id }}">{{ $package->name }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="package_id" />
            </flux:field>

            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('Old version') }}</flux:label>
                    <flux:input wire:model="old_version" placeholder="1.0.0" />
                    <flux:error name="old_version" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('New version') }}</flux:label>
                    <flux:input wire:model="new_version" placeholder="1.1.0" />
                    <flux:error name="new_version" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model="status">
                    <option value="backlog">{{ __('Backlog') }}</option>
                    <option value="checking">{{ __('Checking') }}</option>
                    <option value="checked">{{ __('Checked') }}</option>
                </flux:select>
                <flux:error name="status" />
            </flux:field>

            <div class="flex">
                    @if ($editingChangelogId)
                        <flux:modal.trigger name="delete-changelog">
                            <flux:button wire:click="confirmDelete({{ $editingChangelogId }})" variant="danger">{{ __('Delete') }}</flux:button>
                        </flux:modal.trigger>
                    @endif
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">
                        {{ $editingChangelogId ? __('Update changelog') : __('Create changelog') }}
                    </flux:button>
                </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-changelog" class="md:w-96">
        <form wire:submit="delete" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete changelog') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Are you sure you want to delete this changelog? This action cannot be undone.') }}
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
