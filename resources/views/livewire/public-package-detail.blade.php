<div class="flex flex-col gap-10">
    @push('seo')
        <meta name="description" content="Changelogger overview for {{ $package->name }} — see what changed between versions before you update.">
        <meta property="og:title" content="{{ $package->name }} changelog - Changelogger">
        <meta property="og:description" content="See every checked changelog for {{ $package->name }}.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ route('packages.show', ['vendor' => $vendor, 'name' => $name]) }}">
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "WebPage",
            "name": "{{ $package->name }} changelog",
            "description": "See every checked changelog for {{ $package->name }}.",
            "url": "{{ route('packages.show', ['vendor' => $vendor, 'name' => $name]) }}"
        }
        </script>
    @endpush

    <nav aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-sm text-[#6C6C78]">
            <li><a href="{{ route('home') }}" class="hover:text-[#1B1B20] dark:hover:text-[#EAEAE8]" wire:navigate>All packages</a></li>
            <li aria-hidden="true">/</li>
            <li class="font-mono text-[#1B1B20] dark:text-[#EAEAE8]" aria-current="page">{{ $package->name }}</li>
        </ol>
    </nav>

    <div class="max-w-2xl">
        <h1 class="text-3xl font-semibold tracking-tight text-[#1B1B20] dark:text-[#EAEAE8] lg:text-4xl">
            <span class="font-mono">{{ $package->name }}</span>
        </h1>
        @if ($package->current_version)
            <p class="mt-2 font-mono text-lg text-[#6C6C78]">Currently tracked: {{ $package->current_version }}</p>
        @endif
    </div>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between border-b border-[#E7E7E5] pb-3 dark:border-[#2A2A2D]">
            <h2 class="text-sm font-medium text-[#1B1B20] dark:text-[#EAEAE8]">
                {{ $this->changelogs->count() }} changelog{{ $this->changelogs->count() === 1 ? '' : 's' }}
            </h2>
        </div>

        @if ($this->changelogs->isEmpty())
            <div class="rounded-lg border border-dashed border-[#D4D4D4] bg-white px-6 py-12 text-center dark:border-[#3E3E3A] dark:bg-[#1A1A1D]">
                <p class="text-[#1B1B20] dark:text-[#EAEAE8]">No checked changelogs yet.</p>
                <p class="mt-1 text-sm text-[#6C6C78]">Check back once a changelog has been generated.</p>
            </div>
        @else
            <ul class="flex flex-col divide-y divide-[#E7E7E5] border-b border-[#E7E7E5] dark:divide-[#2A2A2D] dark:border-[#2A2A2D]">
                @foreach ($this->changelogs as $changelog)
                    @php
                        $vendorName = explode('/', $package->name)[0] ?? $package->name;
                        $packageName = explode('/', $package->name)[1] ?? '';
                    @endphp
                    <li class="py-5">
                        <a href="{{ route('changelogs.show', ['vendor' => $vendorName, 'name' => $packageName, 'new_version' => $changelog->new_version]) }}" class="group block focus:outline-none focus-visible:ring-2 focus-visible:ring-[#4F5B93]/50" wire:navigate>
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                                <h3 class="text-base font-semibold text-[#1B1B20] group-hover:text-[#4F5B93] dark:text-[#EAEAE8] dark:group-hover:text-[#5E6B9E]">
                                    {{ $changelog->title ?? $package->name.' changelog' }}
                                </h3>
                                <span class="font-mono text-sm text-[#6C6C78]">{{ $changelog->old_version }} → {{ $changelog->new_version }}</span>
                            </div>
                            @if ($changelog->summary)
                                <p class="mt-2 max-w-prose text-sm leading-relaxed text-[#6C6C78]">{{ $changelog->summary }}</p>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
