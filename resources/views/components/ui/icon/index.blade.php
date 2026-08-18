@props([
    'name' => null,
    'variant' => null,
    'asButton' => false,
])

@php
  $isPhosphorSet = str($name)->doesntStartWith(['hi:']);
  $isHeroiconsSet = !$isPhosphorSet;

  $iconName = $isPhosphorSet ? $name : str($name)->after('hi:');

  if ($isHeroiconsSet) {
      $componentName = match ($variant) {
          'solid', 'outline' => "heroicons::{$variant}.{$iconName}",
          'mini', 'micro' => "heroicons::{$variant}.solid.{$iconName}",
          default => "heroicons::outline.{$iconName}",
      };
  } else {
      // Map variant to @phosphor-icons/web weight class prefix
      $phosphorWeight = match ($variant) {
          'thin'    => 'ph-thin',
          'light'   => 'ph-light',
          'bold'    => 'ph-bold',
          'fill'    => 'ph-fill',
          'duotone' => 'ph-duotone',
          default   => 'ph', // regular
      };
  }

  if ($isPhosphorSet && !str($attributes->get('class'))->contains(['size-', 'w-', 'h-'])) {
      $attributes = $attributes->class('size-5');
  }
@endphp

@if ($asButton)
  <button {{ $attributes->class('cursor-pointer') }} type="button">
@endif

@if ($isHeroiconsSet)
  <x-dynamic-component :component="$componentName"
    {{ $attributes->class(['[:where(&)]:text-neutral-700 [:where(&)]:dark:text-neutral-300']) }} data-slot="icon" />
@else
  {{-- Rendered by @phosphor-icons/web CDN via SVG injection --}}
  <i class="{{ $phosphorWeight }} ph-{{ $iconName }}"
    {{ $attributes->class(['[:where(&)]:text-neutral-700 [:where(&)]:dark:text-neutral-300']) }} data-slot="icon"></i>
@endif

@if ($asButton)
  </button>
@endif
