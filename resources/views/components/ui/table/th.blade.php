@props([
    'uppercase' => true,
    'scope' => 'col',
])

<th scope="{{ $scope }}"
  class="whitespace-nowrap px-6 py-3 text-start text-xs font-semibold text-gray-500 dark:text-neutral-400 {{ $uppercase ? 'uppercase' : '' }}">
  {{ $slot }}
</th>
