<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')

        @stack('seo')
    </head>
    <body class="min-h-screen bg-[#F8F8F6] font-sans text-[#1B1B20] antialiased dark:bg-[#141416] dark:text-[#EAEAE8]">
        <header class="border-b border-[#E7E7E5] bg-white/80 backdrop-blur dark:border-[#2A2A2D] dark:bg-[#1A1A1D]/80">
            <div class="mx-auto flex h-14 max-w-5xl items-center justify-between px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-semibold tracking-tight" wire:navigate>
                    <span class="flex aspect-square size-7 items-center justify-center rounded bg-[#4F5B93] text-white">
                            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
                    </span>
                    <span>
                        {{config('app.name')}}
                    </span>
                </a>

                @if (Route::has('login'))
                    <nav class="flex items-center gap-4 text-sm">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-[#6C6C78] hover:text-[#1B1B20] dark:hover:text-[#EAEAE8]" wire:navigate>
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-[#6C6C78] hover:text-[#1B1B20] dark:hover:text-[#EAEAE8]" wire:navigate>
                                Log in
                            </a>
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-12 lg:py-16">
            {{ $slot }}
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
