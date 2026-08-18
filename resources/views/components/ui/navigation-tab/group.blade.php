@props([
    'orientation' => 'horizontal',
])

@if ($orientation === 'vertical')
  <nav {{ $attributes->class(['flex flex-col gap-2']) }}>
    {{ $slot }}
  </nav>
@else
  <div {{ $attributes->class(['relative overflow-hidden flex justify-center md:justify-start']) }}
    data-hs-scroll-nav="{
            &quot;autoCentering&quot;: true
          }">
    <nav
      class="hs-scroll-nav-body flex flex-nowrap overflow-x-auto [&::-webkit-scrollbar]:h-0 snap-x snap-mandatory pb-1.5">
      {{ $slot }}
    </nav>
  </div>
@endif
