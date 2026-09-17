<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')

        @stack('seo')
    </head>
    <body class="sheet min-h-screen antialiased">
        <div class="sheet-board">
            <div class="sheet-frame">
                <header class="sheet-titleblock">
                    <a href="{{ route('home') }}" class="sheet-titleblock__brand" wire:navigate>
                        <x-sheet-mark class="size-5 shrink-0" />
                        <span class="sheet-wordmark">{{ config('app.name') }}</span>
                    </a>

                    <p class="sheet-titleblock__field">
                        <span class="sheet-label">Drawn from</span>
                        <span>the file-system diff between two versions of a package</span>
                    </p>

                    <nav class="sheet-titleblock__nav" aria-label="Account">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="ghost" size="sm" icon:trailing="arrow-right" class="sheet-navbtn" wire:navigate>
                                Dashboard
                            </flux:button>
                        @else
                            <flux:button href="{{ route('login') }}" variant="ghost" size="sm" icon:trailing="arrow-right" class="sheet-navbtn" wire:navigate>
                                Log in
                            </flux:button>
                        @endauth
                    </nav>
                </header>

                <main class="sheet-main">
                    {{ $slot }}
                </main>

                <footer class="sheet-footer">
                    <span class="sheet-label">{{ config('app.name') }}</span>
                    <span>Conductor generates Composer package changelogs from the actual diff between versions.</span>
                </footer>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
