@props([])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-[var(--color-line)] bg-[var(--color-surface)] p-5 shadow-sm']) }}>
    {{ $slot }}
</div>
