@props([
    'title' => 'Dashboard',
    'href' => route('home'),
    'active' => null,
    'visible' => false,
])

@php
  $activeClasses = 'bg-primary-100 text-primary-600 dark:bg-primary-700/25 dark:text-white';
  $inactiveClasses =
      'text-gray-700 hover:text-gray-900 hover:bg-primary-100 focus:bg-primary-100 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-primary-700/25 dark:focus:bg-primary-700';
@endphp

<li class="{{ $visible ? '' : 'hidden' }}">
  <a wire:navigate @if ($active === null) wire:current="{{ $activeClasses }}" @endif
    class="flex gap-x-4 py-2 px-3 text-sm rounded-lg transition-colors focus:outline-none {{ $active ? $activeClasses : ($active === false ? $inactiveClasses : $inactiveClasses) }}"
    href="{{ $href }}">
    {{ $title }}
  </a>
</li>
