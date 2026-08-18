@props([
    'heading' => null,
    'description' => null,
    'rounded' => true,
    'scrollable' => true,
    'bordered' => true,
    'size' => 'full',
    'variant' => 'default',
    'footer' => null,
])

@php
  $sizeClass = match ($size) {
      'xs' => 'max-w-xs',
      'sm' => 'max-w-sm',
      'md' => 'max-w-md',
      'lg' => 'max-w-lg',
      'xl' => 'max-w-xl',
      '2xl' => 'max-w-2xl',
      '3xl' => 'max-w-3xl',
      '4xl' => 'max-w-4xl',
      '5xl' => 'max-w-5xl',
      '6xl' => 'max-w-6xl',
      '7xl' => 'max-w-7xl',
      'half' => 'w-1/2',
      'full' => 'max-w-full',
  };

  $isResults = $variant === 'results';
  $roundedClass = ($isResults || $footer) ? 'rounded-t-xl' : ($rounded ? 'rounded-xl' : '');
@endphp

<div class="{{ $sizeClass }} space-y-0">
  @if ($heading || $description)
    <div class="space-y-2 mb-4">
      <x-ui.heading level="h4" size="" class="">{{ $heading ?? '' }}</x-ui.heading>
      <x-ui.text>{{ $description ?? '' }}</x-ui.text>
    </div>
  @endif

  <div
    class="dark:bg-gray-900 {{ $roundedClass }} {{ $bordered ? 'border border-gray-200 dark:border-white/10' : '' }} {{ $footer ? 'border-b-0' : '' }} overflow-x-auto [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-thumb]:rounded-none [&::-webkit-scrollbar-track]:bg-gray-100 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">

    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
      {{ $slot }}
    </table>
  </div>

  @if ($footer)
    <x-ui.table.footer :footer="$footer" />
  @endif
</div>
