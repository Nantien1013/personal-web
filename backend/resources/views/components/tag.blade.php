@props([
    'color' => 'accent',
])

@php
$colors = [
    'accent' => 'bg-[var(--color-accent-text)]/10 text-[var(--color-accent-text)]',
    'muted' => 'bg-[var(--color-line)]/60 text-[var(--color-muted)]',
];

$classes = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.($colors[$color] ?? $colors['accent']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
