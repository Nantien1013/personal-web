@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
])

@php
$base = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent)] focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';

$variants = [
    'primary' => 'bg-[var(--color-accent-text)] text-white hover:opacity-90',
    'ghost' => 'bg-transparent text-[var(--color-ink)] border border-[var(--color-line)] hover:bg-[var(--color-surface)]',
];

$classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
