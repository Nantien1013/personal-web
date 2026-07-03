@props([
    'label' => '',
    'value' => '',
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1']) }}>
    <span class="text-sm text-[var(--color-muted)]">{{ $label }}</span>
    <span class="font-mono text-2xl font-semibold">{{ $value }}</span>
</div>
