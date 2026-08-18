@props([
    'cols' => [
        'default' => 1,
        'sm' => 2,
        'lg' => 4,
        // 'xl' => 6,
    ],
    'flex' => false,
])

@php
  $colClasses = collect($cols)
      ->map(function ($count, $breakpoint) {
          return $breakpoint === 'default' ? "grid-cols-{$count}" : "{$breakpoint}:grid-cols-{$count}";
      })
      ->implode(' ');
@endphp

<nav {{ $attributes->class(["grid {$colClasses} gap-2 md:gap-4 w-full", '2xl:flex 2xl:flex-wrap' => $flex]) }}>
  {{ $slot }}
</nav>
