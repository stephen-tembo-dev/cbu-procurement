@props([
    'footer' => '',
])

@php
  $lowerFooter = strtolower($footer);

  if (str_starts_with($lowerFooter, 'clear pass')) {
      $bgClass = 'bg-green-50 border-green-200 dark:bg-green-500/10 dark:border-green-500/20';
      $textClass = 'text-green-800 dark:text-green-400';
  } elseif (str_starts_with($lowerFooter, 'part time')) {
      $bgClass = 'bg-red-50 border-red-200 dark:bg-red-500/10 dark:border-red-500/20';
      $textClass = 'text-red-800 dark:text-red-400';
  } elseif (str_starts_with($lowerFooter, 'proceed and repeat')) {
      $bgClass = 'bg-amber-50 border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20';
      $textClass = 'text-amber-800 dark:text-amber-400';
  } else {
      $bgClass = 'bg-gray-50 border-gray-200 dark:bg-white/5 dark:border-white/10';
      $textClass = 'text-gray-500 dark:text-neutral-400';
  }
@endphp

<div class="px-6 py-3 border border-t-0 rounded-b-xl {{ $bgClass }}">
  <p class="text-sm font-medium {{ $textClass }}">{{ $footer }}</p>
</div>
