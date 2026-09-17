@php
    $facts = $this->facts;
    $latestOverall = $facts['latest'];
    $latestParts = $latestOverall ? explode('/', $latestOverall->package->name, 2) : null;
@endphp

<div class="sheet-page">
    {{-- Title cell and sheet facts --}}
    <section class="sheet-block sheet-block--title" aria-labelledby="sheet-title">
        <div class="sheet-cell sheet-cell--title">
            <flux:heading level="1" id="sheet-title" class="sheet-h1">
                Find changelogs for Composer packages.
            </flux:heading>
            <flux:text class="sheet-lede">
                Search by author or package name to see what changed between versions, before you update.
            </flux:text>
        </div>

        <dl class="sheet-facts">
            <div class="sheet-fact">
                <dt class="sheet-label">Packages</dt>
                <dd class="sheet-fact__value">{{ $facts['packages'] }}</dd>
            </div>
            <div class="sheet-fact">
                <dt class="sheet-label">Checked changelogs</dt>
                <dd class="sheet-fact__value">{{ $facts['changelogs'] }}</dd>
            </div>
            <div class="sheet-fact">
                <dt class="sheet-label">Latest revision</dt>
                <dd class="sheet-fact__value sheet-fact__value--text">
                    @if ($latestOverall)
                        <a
                            href="{{ route('changelogs.show', ['vendor' => $latestParts[0], 'name' => $latestParts[1] ?? '', 'new_version' => $latestOverall->new_version]) }}"
                            wire:navigate
                        >{{ $latestOverall->package->name }} {{ $latestOverall->new_version }}</a>
                        <br>
                        <time datetime="{{ $latestOverall->created_at->toIso8601String() }}" class="sheet-dim">{{ $latestOverall->created_at->format('Y-m-d') }}</time>
                    @else
                        <span class="sheet-dim">None yet</span>
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    {{-- Find package --}}
    <section class="sheet-block sheet-block--search">
        <form wire:submit.prevent class="sheet-search" role="search">
            <flux:label for="search" class="sheet-label">Find package</flux:label>
            <flux:input
                id="search"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="e.g. laravel/framework"
                icon="magnifying-glass"
                clearable
                autocomplete="off"
                spellcheck="false"
                class="sheet-search__input"
            />
        </form>
    </section>

    {{-- Author strip --}}
    @if ($this->vendors->isNotEmpty())
        <section class="sheet-block sheet-block--authors" aria-label="Filter by author">
            <div class="sheet-cell--label">
                <span class="sheet-label">Author</span>
            </div>
            <div class="sheet-authors">
                <button
                    type="button"
                    wire:click="clearVendor"
                    class="sheet-authorcell"
                    aria-pressed="{{ $vendor === null ? 'true' : 'false' }}"
                >
                    All
                    <span class="sheet-authorcell__count">{{ $facts['packages'] }}</span>
                </button>
                @foreach ($this->vendors as $v)
                    <button
                        type="button"
                        wire:click="filterByVendor('{{ $v->vendor }}')"
                        class="sheet-authorcell"
                        aria-pressed="{{ $vendor === $v->vendor ? 'true' : 'false' }}"
                    >
                        {{ $v->vendor }}
                        <span class="sheet-authorcell__count">{{ $v->count }}</span>
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Revision record --}}
    <section class="sheet-block sheet-block--table" aria-labelledby="sheet-revisions">
        <div class="sheet-tablehead">
            <flux:heading level="2" id="sheet-revisions" class="sheet-label">Revision record</flux:heading>
            <span class="sheet-tablehead__count">
                {{ $this->packages->total() }} {{ $this->packages->total() === 1 ? 'package' : 'packages' }}
                @if ($search)
                    matching “{{ $search }}”
                @endif
                @if ($vendor)
                    by {{ $vendor }}
                @endif
            </span>
            @if (($search || $vendor) && $this->packages->isNotEmpty())
                <flux:button wire:click="clearFilters" variant="ghost" size="xs" class="sheet-navbtn sheet-tablehead__clear">
                    Clear filters
                </flux:button>
            @endif
        </div>

        <div
            class="sheet-tablewrap"
            wire:loading.class="is-inking"
            wire:target="search, filterByVendor, clearVendor, clearFilters, previousPage, nextPage"
        >
            @if ($this->packages->isEmpty())
                <div class="sheet-cell sheet-empty">
                    <p class="sheet-empty__title">
                        @if ($search)
                            No package on this sheet matches “{{ $search }}”.
                        @elseif ($vendor)
                            No packages by {{ $vendor }} on this sheet.
                        @else
                            No packages are tracked yet.
                        @endif
                    </p>
                    <p class="sheet-empty__hint">
                        @if ($search || $vendor)
                            Try the author name only, for example “laravel”, or clear the filters to see every tracked package.
                        @else
                            Once a package is tracked and its first changelog is checked, it appears here.
                        @endif
                    </p>
                    @if ($search || $vendor)
                        <flux:button wire:click="clearFilters" variant="ghost" size="sm" class="sheet-navbtn">
                            Clear filters
                        </flux:button>
                    @endif
                </div>
            @else
                @php
                    $newestId = $this->packages->getCollection()
                        ->map(fn ($p) => $p->changelogs->first())
                        ->filter()
                        ->sortByDesc('created_at')
                        ->first()?->id;
                    $firstItem = $this->packages->firstItem() ?? 1;
                @endphp

                <flux:table class="sheet-table">
                    <flux:table.columns>
                        <flux:table.column class="sheet-th sheet-col-rev">Rev</flux:table.column>
                        <flux:table.column class="sheet-th sheet-col-pkg">Package</flux:table.column>
                        <flux:table.column class="sheet-th sheet-col-ver">From → To</flux:table.column>
                        <flux:table.column class="sheet-th sheet-th--breaking sheet-col-n" align="end"><abbr title="Breaking changes">Δ</abbr></flux:table.column>
                        <flux:table.column class="sheet-th sheet-col-n" align="end"><abbr title="New">+</abbr></flux:table.column>
                        <flux:table.column class="sheet-th sheet-col-n" align="end"><abbr title="Updated">~</abbr></flux:table.column>
                        <flux:table.column class="sheet-th">Description</flux:table.column>
                        <flux:table.column class="sheet-th sheet-col-date">Date</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->packages as $pkg)
                            @php
                                $parts = explode('/', $pkg->name, 2);
                                $vendorName = $parts[0];
                                $packageName = $parts[1] ?? '';
                                $latest = $pkg->changelogs->first();
                                $summary = $latest ? $this->changeSummary($latest) : null;
                                $breaking = $latest ? $this->breakingTitles($latest) : collect();
                                $packageUrl = route('packages.show', ['vendor' => $vendorName, 'name' => $packageName]);
                                $changelogUrl = $latest
                                    ? route('changelogs.show', ['vendor' => $vendorName, 'name' => $packageName, 'new_version' => $latest->new_version])
                                    : $packageUrl;
                            @endphp

                            <flux:table.row :key="$pkg->id" class="sheet-tr {{ $latest ? '' : 'sheet-tr--thin' }}">
                                <flux:table.cell class="sheet-td sheet-td--rev">
                                    <span class="sheet-mono">{{ sprintf('%02d', $firstItem + $loop->index) }}</span>
                                </flux:table.cell>

                                <flux:table.cell class="sheet-td sheet-td--pkg">
                                    <a href="{{ $packageUrl }}" class="sheet-pkg" wire:navigate>
                                        <span class="sheet-pkg__vendor">{{ $vendorName }}/</span><wbr><span class="sheet-pkg__name">{{ $packageName }}</span>
                                    </a>
                                </flux:table.cell>

                                <flux:table.cell class="sheet-td sheet-td--ver">
                                    @if ($latest)
                                        <span class="sheet-mono">{{ $latest->old_version }}</span><span class="sheet-arrow" aria-hidden="true">→</span><span class="sheet-mono sheet-mono--strong">{{ $latest->new_version }}</span>
                                    @elseif ($pkg->current_version)
                                        <span class="sheet-mono sheet-dim">tracking {{ $pkg->current_version }}</span>
                                    @else
                                        <span class="sheet-mono sheet-dim">—</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell class="sheet-td sheet-td--n sheet-td--breaking" data-label="Δ" align="end">
                                    @if ($summary && $summary['breaking'] > 0)
                                        @php
                                            $deltaLabel = $summary['breaking'].' breaking '.($summary['breaking'] === 1 ? 'change' : 'changes').' in '.$pkg->name.' '.$latest->new_version;
                                        @endphp

                                        {{-- Fine pointers: opens on hover and keyboard focus. --}}
                                        <span class="sheet-delta-hover">
                                            <flux:tooltip interactive position="bottom" align="end">
                                                <button type="button" class="sheet-delta" aria-label="{{ $deltaLabel }}">
                                                    <x-sheet-delta />
                                                    {{ $summary['breaking'] }}
                                                </button>

                                                <flux:tooltip.content class="sheet-tip">
                                                    @include('livewire.partials.breaking-note', ['pkg' => $pkg, 'latest' => $latest, 'breaking' => $breaking, 'changelogUrl' => $changelogUrl])
                                                </flux:tooltip.content>
                                            </flux:tooltip>
                                        </span>

                                        {{-- Touch: opens on tap. --}}
                                        <span class="sheet-delta-tap">
                                            <flux:tooltip toggleable position="bottom" align="start">
                                                <button type="button" class="sheet-delta" aria-label="{{ $deltaLabel }}">
                                                    <x-sheet-delta />
                                                    {{ $summary['breaking'] }}
                                                </button>

                                                <flux:tooltip.content class="sheet-tip">
                                                    @include('livewire.partials.breaking-note', ['pkg' => $pkg, 'latest' => $latest, 'breaking' => $breaking, 'changelogUrl' => $changelogUrl])
                                                </flux:tooltip.content>
                                            </flux:tooltip>
                                        </span>
                                    @else
                                        <span class="sheet-mono sheet-zero">{{ $summary ? '0' : '—' }}</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell class="sheet-td sheet-td--n" data-label="+" align="end">
                                    <span class="sheet-mono {{ $summary && $summary['new'] > 0 ? '' : 'sheet-zero' }}">{{ $summary ? $summary['new'] : '—' }}</span>
                                </flux:table.cell>

                                <flux:table.cell class="sheet-td sheet-td--n" data-label="~" align="end">
                                    <span class="sheet-mono {{ $summary && $summary['updated'] > 0 ? '' : 'sheet-zero' }}">{{ $summary ? $summary['updated'] : '—' }}</span>
                                </flux:table.cell>

                                <flux:table.cell class="sheet-td sheet-td--desc">
                                    @if ($latest)
                                        <a href="{{ $changelogUrl }}" class="sheet-desc" wire:navigate>
                                            <span class="sheet-desc__title">{{ $latest->title ?? $pkg->name.' '.$latest->old_version.' → '.$latest->new_version }}</span>
                                            @if ($latest->summary)
                                                <span class="sheet-desc__summary">{{ $latest->summary }}</span>
                                            @endif
                                        </a>
                                    @else
                                        <span class="sheet-dim">No published changelog yet.</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell class="sheet-td sheet-td--date">
                                    @if ($latest)
                                        <time datetime="{{ $latest->created_at->toIso8601String() }}" class="sheet-mono">{{ $latest->created_at->format('Y-m-d') }}</time>
                                        @if ($latest->id === $newestId)
                                            <br><span class="sheet-newest">Newest</span>
                                        @endif
                                    @else
                                        <span class="sheet-mono sheet-dim">—</span>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </div>

        <div class="sheet-sheetnav">
            <div class="sheet-legend" aria-label="Legend">
                <span><span class="sheet-mono sheet-mono--red">Δ</span> breaking</span>
                <span><span class="sheet-mono">+</span> new</span>
                <span><span class="sheet-mono">~</span> updated</span>
            </div>

            <nav class="sheet-pager" aria-label="Sheets">
                <flux:button
                    wire:click="previousPage"
                    :disabled="$this->packages->onFirstPage()"
                    variant="ghost"
                    size="sm"
                    icon="chevron-left"
                    class="sheet-navbtn"
                >
                    Prev
                </flux:button>
                <span class="sheet-pager__label">Sheet {{ $this->packages->currentPage() }} of {{ max($this->packages->lastPage(), 1) }}</span>
                <flux:button
                    wire:click="nextPage"
                    :disabled="! $this->packages->hasMorePages()"
                    variant="ghost"
                    size="sm"
                    icon:trailing="chevron-right"
                    class="sheet-navbtn"
                >
                    Next
                </flux:button>
            </nav>
        </div>
    </section>
</div>
