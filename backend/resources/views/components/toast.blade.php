@props([])

<div
    x-data="{ show: false, message: '' }"
    x-on:toast.window="message = $event.detail.message ?? $event.detail; show = true; setTimeout(() => show = false, 3000)"
    x-show="show"
    x-transition
    style="display: none;"
    class="fixed bottom-4 right-4 z-50 rounded-lg border border-[var(--color-line)] bg-[var(--color-surface)] px-4 py-3 text-sm shadow-lg"
>
    <span x-text="message"></span>
</div>
