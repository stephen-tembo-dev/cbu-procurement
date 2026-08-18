@props([
    'name' => $attributes->whereStartsWith('wire:model')->first() ?? $attributes->whereStartsWith('x-model')->first(),
    'label' => null,
    'description' => null,
    'checked' => false,
])

@php
  $invalid = $name && $errors->has($name);
  $hasBody = filled($label) || filled($description) || $slot->isNotEmpty();
@endphp

<label class="inline-flex items-start gap-3 text-start">
  <input type="checkbox" @checked($checked) @if (filled($name)) name="{{ $name }}" @endif
    {{ $attributes->class([
        'mt-0.5 h-4 w-4 rounded border border-black/20 text-sky-600 shadow-sm transition focus:ring-2 focus:ring-sky-200 focus:ring-offset-0',
        'border-red-500 focus:ring-red-200' => $invalid,
    ]) }}
    data-slot="control" />

  @if ($hasBody)
    <span class="space-y-1">
      @if (filled($label) || $slot->isNotEmpty())
        <span class="block text-sm font-medium text-neutral-800 dark:text-white">
          {{ $label ?? $slot }}
        </span>
      @endif

      @if (filled($description))
        <span class="block text-sm text-neutral-500 dark:text-neutral-400">
          {{ $description }}
        </span>
      @endif
    </span>
  @endif
</label>
