@props([
    'title',
    'icon' => null,
    'subtitle' => null,
])

<section class="flex min-h-full flex-col gap-3 rounded-[4px] border border-[#c0c7d0] bg-white p-4 shadow-sm">
    <div class="flex items-start gap-3 border-b border-[#dbe3ec] pb-3">
        @if ($icon)
            <span class="mt-0.5 text-xl opacity-80" aria-hidden="true">{{ $icon }}</span>
        @endif
        <div class="space-y-1">
            <h2 class="text-[15px] font-bold tracking-tight text-[#154f85]">{{ $title }}</h2>
            @if ($subtitle)
                <p class="text-[11px] leading-5 text-[#6b7280]">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="min-h-0 w-full">
        {{ $slot }}
    </div>
</section>
