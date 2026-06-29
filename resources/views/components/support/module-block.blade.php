@props([
    'title',
    'icon' => null,
    'subtitle' => null,
])

<section class="flex min-h-full flex-col gap-4 rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
    <div class="flex items-start gap-3 border-b border-zinc-100 pb-4 dark:border-zinc-800/60">
        @if ($icon)
            <span class="mt-0.5 text-xl opacity-80" aria-hidden="true">{{ $icon }}</span>
        @endif
        <div class="space-y-1">
            <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $title }}</h2>
            @if ($subtitle)
                <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="min-h-0 w-full">
        {{ $slot }}
    </div>
</section>
