@props([
    'message' => '目前沒有資料。',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-[var(--color-line)] p-10 text-center text-[var(--color-muted)]']) }}>
    <p>{{ $message }}</p>
    {{ $slot ?? '' }}
</div>
