@props([
    'href' => route('home'),
    'label' => 'Home',
    'icon' => null,
    'active' => null,
])

<a @class([
    'snap-start inline-flex items-center gap-x-2 py-2 px-3 text-sm whitespace-nowrap border rounded-lg disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:text-gray-500 dark:text-neutral-200 dark:hover:text-neutral-400 dark:focus:text-neutral-400 flex items-center justify-center gap-2 self-start',
    'data-current:bg-white data-current:border-gray-200! data-current:text-gray-800 data-current:dark:text-neutral-200 data-current:focus:text-gray-800 data-current:shadow-2xs data-current:dark:bg-neutral-800 data-current:dark:border-neutral-700! data-current:dark:focus:text-neutral-200',
    'bg-white border-gray-200! text-gray-800 focus:text-gray-800 shadow-2xs dark:bg-neutral-800 dark:border-neutral-700! dark:focus:text-neutral-200' => $active,
    'border-transparent text-gray-800 hover:text-gray-500' => !$active,
]) wire:navigate href="{{ $href }}">
  {{ $label }}
</a>
