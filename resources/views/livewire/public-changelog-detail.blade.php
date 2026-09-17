<div class="flex flex-col gap-10">
    @php
        $parts = explode('/', $changelog->package->name);
        $vendorName = $parts[0] ?? null;
        $packageName = $parts[1] ?? $changelog->package->name;
        $changelogUrl = route('changelogs.show', ['vendor' => $vendorName, 'name' => $packageName, 'new_version' => $changelog->new_version]);
    @endphp

    @push('seo')
        <meta name="description" content="{{ $changelog->summary ? strip_tags($changelog->summary) : 'Changelog for '.$changelog->package->name.' from '.$changelog->old_version.' to '.$changelog->new_version.'.' }}">
        <meta property="og:title" content="{{ $changelog->title ?? $changelog->package->name.' changelog' }} - Changelogger">
        <meta property="og:description" content="{{ $changelog->summary ? strip_tags($changelog->summary) : 'See every breaking, new and updated change.' }}">
        <meta property="og:type" content="article">
        <meta property="og:url" content="{{ $changelogUrl }}">
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@type": "TechArticle",
            "headline": "{{ $changelog->title ?? $changelog->package->name.' changelog' }}",
            "description": "{{ $changelog->summary ? strip_tags($changelog->summary) : 'Changelog for '.$changelog->package->name.' from '.$changelog->old_version.' to '.$changelog->new_version.'.' }}",
            "url": "{{ $changelogUrl }}",
            "datePublished": "{{ $changelog->created_at->toIso8601String() }}",
            "dateModified": "{{ $changelog->updated_at->toIso8601String() }}"
        }
        </script>
    @endpush

    <nav aria-label="Breadcrumb">
        <ol class="flex items-center gap-2 text-sm text-[#6C6C78] flex-wrap">
            <li><a href="{{ route('home') }}" class="hover:text-[#1B1B20] dark:hover:text-[#EAEAE8]" wire:navigate>All packages</a></li>
            <li aria-hidden="true">/</li>
            @php
                $parts = explode('/', $changelog->package->name);
                $vendorName = $parts[0] ?? null;
                $packageName = $parts[1] ?? $changelog->package->name;
            @endphp
            <li>
                <a href="{{ route('packages.show', ['vendor' => $vendorName, 'name' => $packageName]) }}" class="font-mono hover:text-[#1B1B20] dark:hover:text-[#EAEAE8]" wire:navigate>
                    {{ $changelog->package->name }}
                </a>
            </li>
            <li aria-hidden="true">/</li>
            <li class="font-mono text-[#1B1B20] dark:text-[#EAEAE8]" aria-current="page">{{ $changelog->old_version }} → {{ $changelog->new_version }}</li>
        </ol>
    </nav>

    <div class="max-w-2xl">
        <p class="font-mono text-sm text-[#6C6C78]">{{ $changelog->package->name }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1B1B20] dark:text-[#EAEAE8] lg:text-4xl">
            {{ $changelog->title ?? $changelog->package->name.' changelog' }}
        </h1>
        <p class="mt-3 font-mono text-lg text-[#6C6C78]">{{ $changelog->old_version }} → {{ $changelog->new_version }}</p>
    </div>

    @if ($changelog->summary)
        <p class="max-w-prose text-lg leading-relaxed text-[#1B1B20] dark:text-[#EAEAE8]">
            {{ $changelog->summary }}
        </p>
    @endif

    @php
        $changesByType = $changelog->changes->groupBy('type.value');
    @endphp

    <div class="flex flex-col gap-8">
        @foreach ([
            'breaking' => ['label' => 'Breaking changes', 'color' => '#A84A4A'],
            'new' => ['label' => 'New', 'color' => '#4A7C59'],
            'updated' => ['label' => 'Updated', 'color' => '#5E6B9E'],
        ] as $type => $meta)
            @if (($changesByType[$type] ?? collect())->isNotEmpty())
                <section class="flex flex-col gap-3 border-l-2 pl-5" style="border-color: {{ $meta['color'] }}">
                    <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: {{ $meta['color'] }}">
                        {{ $meta['label'] }}
                    </h2>
                    <ul class="flex flex-col gap-4">
                        @foreach ($changesByType[$type] as $change)
                            <li class="flex flex-col gap-1">
                                <span class="text-base font-medium text-[#1B1B20] dark:text-[#EAEAE8]">{{ $change->title }}</span>
                                @if ($change->message)
                                    <span class="max-w-prose text-sm leading-relaxed text-[#6C6C78]">{{ $change->message }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endforeach

        @if ($changelog->changes->isEmpty())
            <p class="text-[#6C6C78]">No detailed changes recorded for this changelog.</p>
        @endif
    </div>
</div>
