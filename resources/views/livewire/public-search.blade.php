<div class="flex flex-col gap-10">
    <div class="max-w-2xl">
        <h1 class="text-3xl font-semibold tracking-tight text-[#1B1B20] dark:text-[#EAEAE8] lg:text-4xl">
            Find changelogs for Composer packages.
        </h1>
        <p class="mt-3 text-lg text-[#6C6C78]">
            Search by author or package name to see what changed between versions, before you update.
        </p>
    </div>

    <div class="flex flex-col gap-6">
        <form wire:submit.prevent class="relative">
            <label for="search" class="sr-only">Search packages</label>
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[#6C6C78]">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                </svg>
            </div>
            <input
                id="search"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="e.g. laravel/framework or ramsey/uuid"
                class="w-full rounded-lg border border-[#E7E7E5] bg-white py-3 pl-11 pr-4 font-mono text-sm text-[#1B1B20] placeholder:text-[#A3A3A3] shadow-sm focus:border-[#4F5B93] focus:outline-none focus:ring-2 focus:ring-[#4F5B93]/20 dark:border-[#2A2A2D] dark:bg-[#1A1A1D] dark:text-[#EAEAE8] dark:focus:border-[#5E6B9E] dark:focus:ring-[#5E6B9E]/20"
            >
        </form>

        @if ($this->vendors->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-[#6C6C78]">Filter by author:</span>
                @foreach ($this->vendors as $v)
                    <button
                        type="button"
                        wire:click="filterByVendor('{{ $v->vendor }}')"
                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-mono transition-colors {{ $vendor === $v->vendor ? 'border-[#4F5B93] bg-[#4F5B93]/10 text-[#4F5B93] dark:border-[#5E6B9E] dark:bg-[#5E6B9E]/10 dark:text-[#5E6B9E]' : 'border-[#E7E7E5] bg-white text-[#6C6C78] hover:border-[#4F5B93] hover:text-[#4F5B93] dark:border-[#2A2A2D] dark:bg-[#1A1A1D] dark:hover:border-[#5E6B9E] dark:hover:text-[#5E6B9E]' }}"
                        aria-pressed="{{ $vendor === $v->vendor ? 'true' : 'false' }}"
                    >
                        {{ $v->vendor }}
                        <span class="text-[10px] opacity-70">({{ $v->count }})</span>
                    </button>
                @endforeach

                @if ($search || $vendor)
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="ml-2 text-xs text-[#6C6C78] underline underline-offset-2 hover:text-[#1B1B20] dark:hover:text-[#EAEAE8]"
                    >
                        Clear filters
                    </button>
                @endif
            </div>
        @endif
    </div>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between border-b border-[#E7E7E5] pb-3 dark:border-[#2A2A2D]">
            <h2 class="text-sm font-medium text-[#1B1B20] dark:text-[#EAEAE8]">
                {{ $this->packages->total() }} package{{ $this->packages->total() === 1 ? '' : 's' }}
            </h2>
        </div>

        @if ($this->packages->isEmpty())
            <div class="rounded-lg border border-dashed border-[#D4D4D4] bg-white px-6 py-12 text-center dark:border-[#3E3E3A] dark:bg-[#1A1A1D]">
                <p class="text-[#1B1B20] dark:text-[#EAEAE8]">No packages found.</p>
                <p class="mt-1 text-sm text-[#6C6C78]">Try a different search or author filter.</p>
            </div>
        @else
            <ul class="flex flex-col divide-y divide-[#E7E7E5] border-b border-[#E7E7E5] dark:divide-[#2A2A2D] dark:border-[#2A2A2D]">
                @foreach ($this->packages as $pkg)
                    @php
                        $vendorName = explode('/', $pkg->name)[0] ?? $pkg->name;
                        $packageName = explode('/', $pkg->name)[1] ?? '';
                        $latest = $pkg->changelogs->first();
                    @endphp

                    <li class="group py-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex flex-1 flex-col gap-1">
                                <a
                                    href="{{ route('packages.show', ['vendor' => $vendorName, 'name' => $packageName]) }}"
                                    class="inline-flex flex-wrap items-baseline gap-2 font-mono text-lg font-semibold text-[#1B1B20] hover:text-[#4F5B93] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#4F5B93]/50 dark:text-[#EAEAE8] dark:hover:text-[#5E6B9E]"
                                    wire:navigate
                                >
                                    {{ $pkg->name }}
                                    @if ($pkg->current_version)
                                        <span class="text-sm text-[#6C6C78]">{{ $pkg->current_version }}</span>
                                    @endif
                                </a>

                                @if ($latest)
                                    <p class="max-w-prose text-sm leading-relaxed text-[#6C6C78]">
                                        {{ $latest->summary ?? 'Changelog from '.$latest->old_version.' to '.$latest->new_version.'.' }}
                                    </p>
                                @else
                                    <p class="text-sm text-[#6C6C78]">No published changelog yet.</p>
                                @endif
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-3 sm:w-48">
                                @if ($latest)
                                    <div class="flex items-center gap-3 text-xs">
                                        @php
                                            $summary = $this->changeSummary($latest);
                                        @endphp

                                        @if ($summary['breaking'] > 0)
                                            <span class="inline-flex items-center gap-1 font-mono text-[#A84A4A]">
                                                <span class="size-1.5 rounded-full bg-[#A84A4A]" aria-hidden="true"></span>
                                                {{ $summary['breaking'] }} breaking
                                            </span>
                                        @endif
                                        @if ($summary['new'] > 0)
                                            <span class="inline-flex items-center gap-1 font-mono text-[#4A7C59]">
                                                <span class="size-1.5 rounded-full bg-[#4A7C59]" aria-hidden="true"></span>
                                                {{ $summary['new'] }} new
                                            </span>
                                        @endif
                                        @if ($summary['updated'] > 0)
                                            <span class="inline-flex items-center gap-1 font-mono text-[#5E6B9E]">
                                                <span class="size-1.5 rounded-full bg-[#5E6B9E]" aria-hidden="true"></span>
                                                {{ $summary['updated'] }} updated
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <a
                                    href="{{ route('packages.show', ['vendor' => $vendorName, 'name' => $packageName]) }}"
                                    class="text-sm font-medium text-[#4F5B93] hover:text-[#3A456E] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#4F5B93]/50 dark:text-[#5E6B9E] dark:hover:text-[#7D8BC0]"
                                    wire:navigate
                                >
                                    See changes
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-2">
                {{ $this->packages->links() }}
            </div>
        @endif
    </div>
</div>
