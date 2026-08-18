<div>
  {{-- Page Header --}}
  <div class="mb-8 flex items-center gap-4">
    <a href="{{ route('requisitions.index') }}"
       class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
      <ph-arrow-left weight="bold" class="text-base"></ph-arrow-left>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">New Purchase Requisition</h1>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">Fill in the details and submit for HOD approval</p>
    </div>
  </div>

  <form wire:submit="submit" class="space-y-6 max-w-4xl">

    {{-- Basic Info Card --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
      <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-5">Requisition Details</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

        {{-- Type --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Type <span class="text-red-500">*</span>
          </label>
          <div class="flex gap-3">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" wire:model.live="type" value="product"
                class="text-emerald-500 focus:ring-emerald-500 dark:bg-neutral-700 dark:border-neutral-600">
              <span class="text-sm text-gray-700 dark:text-neutral-300">Product / Goods</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" wire:model.live="type" value="service"
                class="text-emerald-500 focus:ring-emerald-500 dark:bg-neutral-700 dark:border-neutral-600">
              <span class="text-sm text-gray-700 dark:text-neutral-300">Service</span>
            </label>
          </div>
          @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Cost Centre --}}
        <div>
          <label for="costCentreId" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Cost Centre <span class="text-red-500">*</span>
          </label>
          <select id="costCentreId" wire:model="costCentreId"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">Select cost centre…</option>
            @foreach($this->costCentres as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
          @if(empty($this->costCentres))
            <p class="text-amber-600 text-xs mt-1">No cost centres available for your department.</p>
          @endif
          @error('costCentreId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Service Date (conditional) --}}
        @if($type === 'service')
        <div>
          <label for="serviceDate" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Service Date <span class="text-red-500">*</span>
          </label>
          <input id="serviceDate" type="date" wire:model="serviceDate"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('serviceDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        @endif

        {{-- Notes --}}
        <div class="sm:col-span-2">
          <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Justification / Notes
          </label>
          <textarea id="notes" wire:model="notes" rows="3"
            placeholder="Explain the purpose and justification for this requisition…"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"></textarea>
        </div>

      </div>
    </div>

    {{-- Items Card --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Line Items</h2>
        <span class="text-sm text-gray-500 dark:text-neutral-400">
          Estimated Total:
          <span class="font-semibold text-gray-900 dark:text-white">
            ZMW {{ number_format($this->computedTotal(), 2) }}
          </span>
        </span>
      </div>

      <div class="space-y-4">
        @foreach($items as $index => $item)
        @php
          $catalogForCategory = $item['category'] ? ($this->stockCatalog[$item['category']] ?? []) : [];
          $hasCatalogItems    = count($catalogForCategory) > 0;
          $stockLinked        = ! empty($item['stock_item_id']);
          $showCatalogPicker  = $hasCatalogItems && ! $item['manual_entry'] && $item['category'];
        @endphp
        <div class="p-4 bg-gray-50 dark:bg-neutral-700/50 rounded-lg border border-gray-200 dark:border-neutral-600">
          <div class="flex items-start justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">
              Item {{ $index + 1 }}
            </span>
            @if(count($items) > 1)
            <button type="button" wire:click="removeItem({{ $index }})"
              class="text-red-400 hover:text-red-600 transition-colors">
              <ph-trash weight="bold" class="text-base"></ph-trash>
            </button>
            @endif
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

            {{-- Category --}}
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Category</label>
              <select wire:model="items.{{ $index }}.category"
                x-on:change="$wire.onCategoryChanged({{ $index }})"
                class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">— Select category —</option>
                @foreach($this->categories as $cat)
                  <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
              </select>
            </div>

            {{-- Catalog picker (when category has stock items and user hasn't chosen "not listed") --}}
            @if($showCatalogPicker)
            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">
                Select from catalog <span class="text-red-500">*</span>
              </label>
              <select
                x-on:change="$wire.onStockItemSelected({{ $index }}, $event.target.value)"
                class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">— Search catalog —</option>
                @foreach($catalogForCategory as $catalogItem)
                  <option value="{{ $catalogItem['id'] }}"
                    {{ (int)($item['stock_item_id'] ?? 0) === $catalogItem['id'] ? 'selected' : '' }}>
                    {{ $catalogItem['label'] }}
                  </option>
                @endforeach
                <option value="__none__">✎ Not listed — specify manually</option>
              </select>
            </div>
            @endif

            {{-- Description: read-only chip if catalog item selected, free-form otherwise --}}
            @if($stockLinked)
            <div class="sm:col-span-2 @if(!$showCatalogPicker) lg:col-span-2 @endif">
              <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">
                Description <span class="text-xs font-normal text-emerald-600 dark:text-emerald-400">(from catalog)</span>
              </label>
              <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20">
                <ph-check-circle weight="fill" class="text-emerald-500 flex-shrink-0 text-sm"></ph-check-circle>
                <span class="text-sm text-gray-900 dark:text-white flex-1">{{ $item['description'] }}</span>
                <input type="hidden" wire:model="items.{{ $index }}.description">
              </div>
            </div>
            @else
            <div class="{{ $showCatalogPicker ? 'sm:col-span-3' : 'sm:col-span-2' }}">
              <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">
                Description <span class="text-red-500">*</span>
                @if($item['manual_entry'])
                <span class="text-xs font-normal text-amber-600 dark:text-amber-400">(not in catalog — one-off item)</span>
                @endif
              </label>
              <input type="text" wire:model="items.{{ $index }}.description"
                placeholder="{{ $item['manual_entry'] ? 'Describe the item…' : 'Describe the item or service…' }}"
                class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
              @error("items.{$index}.description") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            @endif

            {{-- Quantity --}}
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">
                Quantity <span class="text-red-500">*</span>
              </label>
              <input type="number" wire:model.live="items.{{ $index }}.quantity"
                min="0.01" step="0.01" placeholder="1"
                class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
              @error("items.{$index}.quantity") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Unit of Measure: read-only if catalog item selected --}}
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Unit</label>
              @if($stockLinked && $item['unit_of_measure'])
              <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 text-sm text-gray-900 dark:text-white">
                {{ $item['unit_of_measure'] }}
                <input type="hidden" wire:model="items.{{ $index }}.unit_of_measure">
              </div>
              @else
              <select wire:model="items.{{ $index }}.unit_of_measure"
                class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option value="">— Select unit —</option>
                @foreach($this->units as $unit)
                  @php $abbr = preg_match('/\(([^)]+)\)$/', $unit, $m) ? $m[1] : $unit; @endphp
                  <option value="{{ $abbr }}" {{ $item['unit_of_measure'] === $abbr ? 'selected' : '' }}>{{ $unit }}</option>
                @endforeach
              </select>
              @endif
            </div>

            {{-- Unit Price --}}
            <div>
              <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">
                Est. Unit Price (ZMW) <span class="text-red-500">*</span>
              </label>
              <input type="number" wire:model.live="items.{{ $index }}.unit_price_estimated"
                min="0" step="0.01" placeholder="0.00"
                class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
              @error("items.{$index}.unit_price_estimated") <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

          </div>

          {{-- Item subtotal --}}
          <div class="mt-3 text-right">
            <span class="text-xs text-gray-500 dark:text-neutral-400">
              Subtotal:
              <span class="font-semibold text-gray-800 dark:text-neutral-200">
                ZMW {{ number_format((float)($item['quantity'] ?? 0) * (float)($item['unit_price_estimated'] ?? 0), 2) }}
              </span>
            </span>
          </div>
        </div>
        @endforeach
      </div>

      {{-- Add Item --}}
      <button type="button" wire:click="addItem"
        class="mt-4 w-full flex items-center justify-center gap-2 px-4 py-2.5 border-2 border-dashed border-gray-300 dark:border-neutral-600 rounded-lg text-sm text-gray-500 dark:text-neutral-400 hover:border-emerald-400 hover:text-emerald-600 dark:hover:border-emerald-500 dark:hover:text-emerald-400 transition-colors">
        <ph-plus weight="bold"></ph-plus>
        Add Another Item
      </button>
    </div>

    {{-- Submit --}}
    <div class="flex items-center justify-between">
      <a href="{{ route('requisitions.index') }}"
         class="px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
        Cancel
      </a>
      <button type="submit"
        class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-emerald-500/20 disabled:opacity-50"
        wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="submit">
          <ph-paper-plane-tilt weight="fill" class="text-base"></ph-paper-plane-tilt>
        </span>
        <span wire:loading wire:target="submit">
          <x-ui.icon.loading class="text-base" />
        </span>
        <span wire:loading.remove wire:target="submit">Submit Requisition</span>
        <span wire:loading wire:target="submit">Submitting…</span>
      </button>
    </div>

  </form>
</div>
