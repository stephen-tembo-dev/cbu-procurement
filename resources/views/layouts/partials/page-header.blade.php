  <div
    class="flex flex-col justify-between gap-y-1 mb-2 lg:mb-3 -mt-2 sticky top-14.5 z-30  backdrop-blur-lg py-2 rounded-md -translate-x-7 w-screen ps-7">
    <div class="flex items-center gap-2">
      @if ($backRoute ?? false)
        <div class="inline-block">
          <x-ui.button class="self-start rounded-full ps-1.75! pe-1.75!" variant="ghost" size="sm"
            href="{{ $backRoute }}" wire:navigate><x-ui.icon name="caret-left" /></x-ui.button>
        </div>
      @endif

      <x-ui.heading level="h3" size="lg">{{ $headerTitle ?? 'Page Title' }}</x-ui.heading>
    </div>

    <x-ui.text class="text-base opacity-50">{{ $headerDescription ?? '' }}</x-ui.text>
  </div>
