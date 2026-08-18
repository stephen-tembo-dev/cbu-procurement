@props([
    'icon' => null,
    'value' => null,
    'label' => '',
    'title' => null,
    'color' => 'primary',
    'variant' => 'default',
    'change' => null,
])

@if ($variant === 'compact')
  <div
    {{ $attributes->class([
        'p-3 flex flex-col border rounded-xl space-y-1',
        'border-neutral-200 dark:border-neutral-700',
        'bg-white dark:bg-neutral-50/5',
    ]) }}>
    <div class="flex gap-x-2 items-center">
      @if ($icon)
        <span
          class="size-8 bg-{{ $color }}-100 rounded-full flex items-center justify-center dark:bg-{{ $color }}-500/20">
          <x-ui.icon :name="$icon" class="text-{{ $color }}-600 dark:text-{{ $color }}-500" />
        </span>
      @endif
      @if ($title)
        <x-ui.text class="text-gray-500! dark:text-neutral-400!">{{ $title }}</x-ui.text>
      @endif
    </div>

    <div class="flex items-center gap-x-1.5">
      @if ($value !== null)
        <p class="text-xl font-medium text-gray-700 dark:text-neutral-200">
          {{ $value }}
        </p>
      @endif
      @if ($change)
        <span @class([
            'inline-flex items-center gap-x-1 text-sm rounded-full',
            'text-green-600 dark:text-green-500' => str_starts_with($change, '+'),
            'text-red-600 dark:text-red-500' => str_starts_with($change, '-'),
            'text-gray-500 dark:text-neutral-400' =>
                !str_starts_with($change, '+') && !str_starts_with($change, '-'),
        ])>
          {{ $change }}
        </span>
      @endif
    </div>
  </div>
@else
  <div
    {{ $attributes->class([
        '2xl:w-41.5 whitespace-nowrap p-2 sm:p-2.5 block text-start text-xs sm:text-sm rounded-xl shadow-xs',
        'bg-white text-gray-800',
        'dark:bg-neutral-800 dark:text-neutral-200',
        'border border-black/10 dark:border-white/10',
    ]) }}>
    <span class="h-full flex flex-col gap-y-2 sm:gap-y-3">
      <span class="flex justify-between gap-x-3">
        @if ($icon)
          <span
            class="flex shrink-0 justify-center items-center size-7 sm:size-8 rounded-full bg-{{ $color }}-100 text-{{ $color }}-600 dark:bg-{{ $color }}-500/20 dark:text-{{ $color }}-500">
            <x-ui.icon :name="$icon" />
          </span>
        @endif
        @if ($value !== null)
          <span class="text-sm font-medium">
            {{ $value }}
          </span>
        @endif
      </span>
      <span class="block ps-1">{{ $label }}</span>
    </span>
  </div>
@endif
