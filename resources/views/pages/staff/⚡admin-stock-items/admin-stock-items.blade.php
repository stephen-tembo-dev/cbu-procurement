<div>

  {{-- Flash --}}
  @if($flash)
  <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-xl border {{ $flashType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-300' : 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300' }}">
    @if($flashType === 'success')<ph-check-circle weight="fill" class="text-xl flex-shrink-0 mt-0.5"></ph-check-circle>
    @else<ph-warning-circle weight="fill" class="text-xl flex-shrink-0 mt-0.5"></ph-warning-circle>@endif
    <span class="text-sm flex-1">{{ $flash }}</span>
    <button wire:click="dismissFlash" class="opacity-60 hover:opacity-100"><ph-x weight="bold" class="text-sm"></ph-x></button>
  </div>
  @endif

  {{-- Header --}}
  <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Stock Items</h1>
      <div class="flex flex-wrap gap-1 mt-2">
        @foreach([['admin.users','Users'],['admin.departments','Departments'],['admin.suppliers','Suppliers'],['admin.stock-items','Stock Items'],['admin.budget','Budget']] as [$route,$label])
        <a href="{{ route($route) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-colors {{ $activeSection === last(explode('.',$route)) ? 'bg-emerald-500 text-white' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">{{ $label }}</a>
        @endforeach
      </div>
    </div>
    @if(auth()->user()->hasRole('admin'))
    <button wire:click="openCreate"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-emerald-500/20 flex-shrink-0">
      <ph-plus weight="bold" class="text-base"></ph-plus>
      Add Stock Item
    </button>
    @endif
  </div>

  {{-- Tabs --}}
  <div class="mb-4 flex gap-1 bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-1.5">
    <button wire:click="switchTab('all')"
            class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                   {{ $activeTab === 'all' ? 'bg-emerald-500 text-white shadow-sm' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">
      <ph-list weight="{{ $activeTab === 'all' ? 'fill' : 'regular' }}" class="text-base"></ph-list>
      All Items
    </button>
    <button wire:click="switchTab('recurring')"
            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                   {{ $activeTab === 'recurring' ? 'bg-emerald-500 text-white shadow-sm' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">
      <ph-arrow-clockwise weight="{{ $activeTab === 'recurring' ? 'fill' : 'regular' }}" class="text-base"></ph-arrow-clockwise>
      Recurring
      @if($this->recurringCount > 0)
      <span class="px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'recurring' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-neutral-300' }}">
        {{ $this->recurringCount }}
      </span>
      @endif
    </button>
    <button wire:click="switchTab('due')"
            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                   {{ $activeTab === 'due' ? 'bg-red-500 text-white shadow-sm' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">
      <ph-warning weight="{{ $activeTab === 'due' ? 'fill' : 'regular' }}" class="text-base {{ $activeTab !== 'due' && $this->dueCount > 0 ? 'text-red-500' : '' }}"></ph-warning>
      Due for Reorder
      @if($this->dueCount > 0)
      <span class="px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'due' ? 'bg-white/20 text-white' : 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' }}">
        {{ $this->dueCount }}
      </span>
      @endif
    </button>
  </div>

  {{-- Table --}}
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700">
    <div class="p-2">
      {{ $this->table }}
    </div>
  </div>

  {{-- Create / Edit Modal --}}
  @if($showModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.closeModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-lg p-6 overflow-y-auto max-h-[90vh]">

      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ $editingId ? 'Edit Stock Item' : 'Add Stock Item' }}
        </h2>
        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 transition-colors">
          <ph-x weight="bold" class="text-lg"></ph-x>
        </button>
      </div>

      <div class="space-y-4">

        {{-- Stock Code --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Stock Code <span class="text-red-500">*</span>
          </label>
          <input type="text" wire:model="stockCode" placeholder="e.g. STK-001"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('stockCode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Description --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Description <span class="text-red-500">*</span>
          </label>
          <input type="text" wire:model="description" placeholder="Item description"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Category --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Category</label>
          <select wire:model="category"
                  class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">— Select category —</option>
            @foreach($this->catalogCategories as $cat)
              <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
          </select>
        </div>

        {{-- Qty / Reorder level --}}
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Quantity on Hand</label>
            <input type="number" step="0.01" min="0" wire:model="quantityOnHand"
                   class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            @error('quantityOnHand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Reorder Level</label>
            <input type="number" step="0.01" min="0" wire:model="reorderLevel"
                   class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            @error('reorderLevel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Unit of Measure --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Unit of Measure</label>
          <select wire:model="unitOfMeasure"
                  class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">— Select unit —</option>
            @foreach($this->catalogUnits as $abbr => $label)
              <option value="{{ $abbr }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        {{-- Flags --}}
        <div class="flex flex-col sm:flex-row gap-4 pt-1">
          <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input type="checkbox" wire:model="isStocked"
                   class="w-4 h-4 rounded border-gray-300 dark:border-neutral-600 text-emerald-500 focus:ring-emerald-500">
            <span class="text-sm font-medium text-gray-700 dark:text-neutral-300">Is Stocked</span>
          </label>
          <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input type="checkbox" wire:model.live="isRecurring"
                   class="w-4 h-4 rounded border-gray-300 dark:border-neutral-600 text-emerald-500 focus:ring-emerald-500">
            <span class="text-sm font-medium text-gray-700 dark:text-neutral-300">Recurring / Periodic</span>
          </label>
          <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input type="checkbox" wire:model="isActive"
                   class="w-4 h-4 rounded border-gray-300 dark:border-neutral-600 text-emerald-500 focus:ring-emerald-500">
            <span class="text-sm font-medium text-gray-700 dark:text-neutral-300">Is Active</span>
          </label>
        </div>

        {{-- Recurring section — shown only when isRecurring is checked --}}
        @if($isRecurring)
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-4 space-y-3">
          <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 uppercase tracking-wide flex items-center gap-1.5">
            <ph-arrow-clockwise weight="fill" class="text-sm"></ph-arrow-clockwise>
            Reorder Schedule
          </p>

          {{-- Frequency preset --}}
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
              Reorder Frequency <span class="text-red-500">*</span>
            </label>
            <select wire:model.live="frequencyPreset"
                    class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
              <option value="7">Weekly (every 7 days)</option>
              <option value="14">Bi-weekly (every 14 days)</option>
              <option value="30">Monthly (every 30 days)</option>
              <option value="60">Every 2 months</option>
              <option value="91">Quarterly (every 91 days)</option>
              <option value="182">Bi-annually (every 182 days)</option>
              <option value="365">Annually (every 365 days)</option>
              <option value="custom">Custom…</option>
            </select>
          </div>

          {{-- Custom days input --}}
          @if($frequencyPreset === 'custom')
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
              Custom interval (days) <span class="text-red-500">*</span>
            </label>
            <input type="number" min="1" max="3650" wire:model="reorderFrequencyDays"
                   placeholder="e.g. 45"
                   class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            @error('reorderFrequencyDays') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>
          @endif

          {{-- Last reorder date --}}
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
              Last Reorder Date
              <span class="text-xs text-gray-400 font-normal">(optional — leave blank if never ordered)</span>
            </label>
            <input type="date" wire:model="lastReorderDate"
                   class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            @error('lastReorderDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Computed next due date preview --}}
          @if($lastReorderDate && $reorderFrequencyDays && $reorderFrequencyDays > 0)
          @php
            $nextDue = \Carbon\Carbon::parse($lastReorderDate)->addDays((int)$reorderFrequencyDays);
          @endphp
          <p class="text-xs text-emerald-700 dark:text-emerald-300">
            <ph-calendar-check weight="fill" class="inline text-sm"></ph-calendar-check>
            Next reorder will be due: <strong>{{ $nextDue->format('d M Y') }}</strong>
            ({{ $nextDue->diffForHumans() }})
          </p>
          @elseif(!$lastReorderDate)
          <p class="text-xs text-amber-600 dark:text-amber-400">
            <ph-warning weight="fill" class="inline text-sm"></ph-warning>
            No last reorder date set — this item will appear as overdue immediately.
          </p>
          @endif

        </div>
        @endif

      </div>

      {{-- Actions --}}
      <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-gray-200 dark:border-neutral-700">
        <button wire:click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="save"
                class="px-5 py-2 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors"
                wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update Item' : 'Create Item' }}</span>
          <span wire:loading wire:target="save">Saving…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

  {{-- Record Reorder Modal --}}
  @if($showReorderModal)
  @php $reorderItem = $reorderingItemId ? \App\Models\StockItem::find($reorderingItemId) : null; @endphp
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.closeReorderModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeReorderModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-sm p-6">

      <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
        <ph-arrow-clockwise weight="fill" class="text-emerald-500 text-lg"></ph-arrow-clockwise>
        Record Reorder
      </h3>
      @if($reorderItem)
      <p class="text-sm text-gray-500 dark:text-neutral-400 mb-4">
        <span class="font-medium text-gray-700 dark:text-neutral-200">{{ $reorderItem->stock_code }}</span>
        — {{ $reorderItem->description }}
      </p>
      @endif

      <div class="space-y-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Reorder Date <span class="text-red-500">*</span>
          </label>
          <input type="date" wire:model="reorderDate"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('reorderDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        @if($reorderItem && $reorderItem->reorder_frequency_days && $reorderDate)
        @php
          $preview = \Carbon\Carbon::parse($reorderDate)->addDays($reorderItem->reorder_frequency_days);
        @endphp
        <p class="text-xs text-emerald-700 dark:text-emerald-300">
          <ph-calendar-check weight="fill" class="inline text-sm"></ph-calendar-check>
          Next due: <strong>{{ $preview->format('d M Y') }}</strong>
        </p>
        @endif
      </div>

      <div class="mt-5 flex justify-end gap-3">
        <button wire:click="closeReorderModal"
                class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-neutral-400 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
          Cancel
        </button>
        <button wire:click="confirmReorder" wire:loading.attr="disabled"
                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
          <span wire:loading.remove wire:target="confirmReorder">Confirm</span>
          <span wire:loading wire:target="confirmReorder">Saving…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

</div>
