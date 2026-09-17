{{-- The revision note behind a red delta: the breaking changes of one changelog. --}}
<p class="sheet-tip__title">Breaking in {{ $pkg->name }} {{ $latest->new_version }}</p>
<ol class="sheet-tip__list">
    @foreach ($breaking->take(5) as $title)
        <li>{{ $title }}</li>
    @endforeach
</ol>
@if ($breaking->count() > 5)
    <p class="sheet-tip__more">+ {{ $breaking->count() - 5 }} more</p>
@endif
<a href="{{ $changelogUrl }}" class="sheet-tip__link" wire:navigate>See every change</a>
