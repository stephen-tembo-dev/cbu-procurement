@props([
    'title' => 'Dropdown',
    'icon' => 'house',
    'active' => null,
])

@php
  $isActive = $active ?? str_contains($slot, request()->url());
@endphp

<li class="w-full px-2 lg:px-5 {{ $isActive ? 'active' : '' }}"
  x-data="{ open: @js($isActive) }">
  <button type="button" @click="open = !open"
    class="w-full text-start flex items-center gap-x-3 py-2 px-3 text-sm text-gray-800 rounded-lg hover:bg-primary-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-primary-100 dark:hover:bg-primary-700/25 dark:text-neutral-300 dark:focus:bg-primary-700/25"
    x-bind:aria-expanded="open ? 'true' : 'false'">

    <x-ui.icon name="{{ $icon }}" class="size-5" />

    {{ $title }}

    <x-ui.icon name="caret-down" class="size-3 ms-auto transition-transform" variant="bold"
      x-bind:class="open ? '-rotate-180' : ''" />

  </button>

  <div class="w-full" role="region"
    x-bind:style="open ? 'display: block;' : 'display: none;'" style="{{ $isActive ? 'display: block;' : 'display: none;' }}">
    <ul
      class="hs-accordion-group ps-7 mt-1 flex flex-col gap-y-1 relative before:absolute before:top-0 before:start-4.5 before:w-0.5 before:h-full before:bg-gray-100 dark:before:bg-neutral-700"
      data-hs-accordion-always-open="">

      {{ $slot }}

    </ul>
  </div>
</li>
