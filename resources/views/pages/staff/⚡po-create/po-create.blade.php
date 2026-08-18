@php
    $pr = $this->pr;
@endphp

<div>

  {{-- Flash message --}}
  @if($flash)
  <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-xl border
    {{ $flashType === 'success'
        ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-300'
        : 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300' }}">
    @if($flashType === 'success')
      <ph-check-circle weight="fill" class="text-xl flex-shrink-0 mt-0.5"></ph-check-circle>
    @else
      <ph-warning-circle weight="fill" class="text-xl flex-shrink-0 mt-0.5"></ph-warning-circle>
    @endif
    <span class="text-sm flex-1">{{ $flash }}</span>
    <button wire:click="dismissFlash" class="opacity-60 hover:opacity-100 transition-opacity">
      <ph-x weight="bold" class="text-sm"></ph-x>
    </button>
  </div>
  @endif

  {{-- Page header --}}
  <div class="mb-8 flex items-start gap-4">
    <a href="{{ route('requisitions.show', $pr->id) }}"
       class="mt-1 w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors flex-shrink-0">
      <ph-arrow-left weight="bold" class="text-base"></ph-arrow-left>
    </a>
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Purchase Order</h1>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
        For requisition
        <span class="font-medium text-gray-700 dark:text-neutral-300">{{ $pr->reference_no }}</span>
        &middot; {{ $pr->department->name }}
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: main form --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- Order details --}}
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Order Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
              Awarded Supplier <span class="text-red-500">*</span>
            </label>
            <select wire:model="supplierId"
              class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
              <option value="0">Select supplier…</option>
              @foreach($this->suppliers as $sid => $name)
                <option value="{{ $sid }}">{{ $name }}</option>
              @endforeach
            </select>
            @error('supplierId')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
              Expected Delivery Date <span class="text-red-500">*</span>
            </label>
            <input type="date" wire:model="deliveryDate"
              class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            @error('deliveryDate')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

        </div>
      </div>

      {{-- Line items --}}
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Line Items</h2>
          <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
            Adjust quantities and enter the actual quoted price for each item.
          </p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-neutral-700/50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Description</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider w-24">Qty</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider w-28">Unit</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider w-36">Unit Price (ZMW)</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider w-32">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
              @foreach($items as $i => $item)
              <tr class="align-top">
                <td class="px-4 py-3">
                  <input type="text" wire:model.live="items.{{ $i }}.description"
                    class="w-full rounded-md border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-transparent">
                  @error("items.{$i}.description")
                    <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                  @enderror
                </td>
                <td class="px-4 py-3">
                  <input type="number" wire:model.live="items.{{ $i }}.quantity"
                    min="0.01" step="0.01"
                    class="w-full rounded-md border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-2 py-1.5 text-right focus:ring-1 focus:ring-emerald-500 focus:border-transparent">
                  @error("items.{$i}.quantity")
                    <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                  @enderror
                </td>
                <td class="px-4 py-3">
                  <select wire:model="items.{{ $i }}.unit_of_measure"
                    class="w-full rounded-md border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">—</option>
                    @foreach($this->units as $abbr => $label)
                      <option value="{{ $abbr }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </td>
                <td class="px-4 py-3">
                  <input type="number" wire:model.live="items.{{ $i }}.unit_price"
                    min="0.01" step="0.01" placeholder="0.00"
                    class="w-full rounded-md border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-2 py-1.5 text-right focus:ring-1 focus:ring-emerald-500 focus:border-transparent">
                  @error("items.{$i}.unit_price")
                    <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                  @enderror
                </td>
                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                  {{ number_format((float) ($item['total_price'] ?? 0), 2) }}
                </td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-neutral-700/50">
              <tr>
                <td colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-neutral-300">
                  PO Total (ZMW)
                </td>
                <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                  {{ number_format(collect($items)->sum(fn ($i) => (float) ($i['total_price'] ?? 0)), 2) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-end gap-3">
        <a href="{{ route('requisitions.show', $pr->id) }}"
           class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </a>
        <button wire:click="submit"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-60"
          wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="submit">
            <ph-file-text weight="bold" class="inline mr-1"></ph-file-text>
            Create Purchase Order
          </span>
          <span wire:loading wire:target="submit">Creating…</span>
        </button>
      </div>

    </div>

    {{-- Right: context panel --}}
    <div class="space-y-6">

      {{-- PR Summary --}}
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Requisition Summary</h2>
        <dl class="space-y-2.5 text-sm">
          <div class="flex justify-between">
            <dt class="text-gray-500 dark:text-neutral-400">Type</dt>
            <dd class="font-medium text-gray-900 dark:text-white capitalize">{{ $pr->type }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-gray-500 dark:text-neutral-400">Department</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $pr->department->name }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-gray-500 dark:text-neutral-400">Cost Centre</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $pr->costCentre->name }}</dd>
          </div>
          <div class="flex justify-between border-t border-gray-100 dark:border-neutral-700 pt-2.5">
            <dt class="text-gray-500 dark:text-neutral-400">Est. Total</dt>
            <dd class="font-bold text-gray-900 dark:text-white">
              ZMW {{ number_format($pr->totalEstimated(), 2) }}
            </dd>
          </div>
        </dl>
      </div>

      {{-- Supplier Quotes Reference --}}
      @if($pr->supplierQuotes->isNotEmpty())
      @php
        $lowestAmount = $pr->supplierQuotes->whereNotNull('amount')->min('amount');
        $responded    = $pr->supplierQuotes->whereNotNull('amount')->sortBy('amount');
        $noResponse   = $pr->supplierQuotes->whereNull('amount');
      @endphp
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-neutral-700">
          <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Supplier Quotes</h2>
          <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">Select the winning supplier above.</p>
        </div>
        <ul class="divide-y divide-gray-100 dark:divide-neutral-700">
          @foreach($responded as $quote)
          <li class="px-5 py-3 flex items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $quote->supplier->name }}</p>
              @if($quote->received_date)
              <p class="text-xs text-gray-400 dark:text-neutral-500">{{ $quote->received_date->format('d M Y') }}</p>
              @endif
            </div>
            <div class="text-right flex-shrink-0">
              <p class="text-sm font-bold {{ $quote->amount == $lowestAmount ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-900 dark:text-white' }}">
                ZMW {{ number_format($quote->amount, 2) }}
              </p>
              @if($quote->amount == $lowestAmount)
              <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <ph-trophy weight="fill" class="inline"></ph-trophy> Lowest
              </span>
              @endif
            </div>
          </li>
          @endforeach
          @foreach($noResponse as $quote)
          <li class="px-5 py-3 flex items-center justify-between gap-3 opacity-60">
            <p class="text-sm text-gray-700 dark:text-neutral-300">{{ $quote->supplier->name }}</p>
            <p class="text-xs text-gray-400 dark:text-neutral-500 italic">No response</p>
          </li>
          @endforeach
        </ul>
      </div>
      @endif

    </div>

  </div>
</div>
