@props([
    'label',
    'value' => null,
])

<div class="space-y-1 text-sm">
  <p class="text-neutral-500 dark:text-neutral-400">{{ $label }}</p>
  <p class="font-medium text-neutral-900 dark:text-neutral-100">{{ filled($value) ? $value : 'N/A' }}</p>
</div>
