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
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Budget Allocations</h1>
      <div class="flex flex-wrap gap-1 mt-2">
        @foreach([['admin.users','Users'],['admin.departments','Departments'],['admin.suppliers','Suppliers'],['admin.stock-items','Stock Items'],['admin.budget','Budget']] as [$route,$label])
        <a href="{{ route($route) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-colors {{ $activeSection === last(explode('.',$route)) ? 'bg-emerald-500 text-white' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">{{ $label }}</a>
        @endforeach
      </div>
    </div>

    <div class="flex items-center gap-3 flex-shrink-0">
      {{-- Fiscal Year Selector --}}
      <div class="flex items-center gap-2">
        <label class="text-sm font-medium text-gray-700 dark:text-neutral-300 whitespace-nowrap">Fiscal Year</label>
        <select wire:model.live="fiscalYear"
                class="rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @for ($y = 2020; $y <= 2030; $y++)
            <option value="{{ $y }}">{{ $y }}</option>
          @endfor
        </select>
      </div>

      {{-- Add Button --}}
      <button wire:click="openCreate"
              class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-emerald-500/20">
        <ph-plus weight="bold" class="text-base"></ph-plus>
        Add Allocation
      </button>
    </div>
  </div>

  {{-- Table --}}
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700">
    <div class="p-2">
      {{ $this->table }}
    </div>
  </div>

  {{-- Modal --}}
  @if($showModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.closeModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-lg p-6">

      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ $editingId ? 'Edit Budget Allocation' : 'Add Budget Allocation' }}
        </h2>
        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 transition-colors">
          <ph-x weight="bold" class="text-lg"></ph-x>
        </button>
      </div>

      <div class="space-y-4">

        {{-- Cost Centre --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Cost Centre <span class="text-red-500">*</span>
          </label>
          <select wire:model="costCentreId"
                  class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="0">— Select cost centre —</option>
            @foreach($this->costCentres as $id => $label)
              <option value="{{ $id }}">{{ $label }}</option>
            @endforeach
          </select>
          @error('costCentreId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Fiscal Year --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Fiscal Year <span class="text-red-500">*</span>
          </label>
          <input type="number" wire:model="modalFiscalYear" placeholder="e.g. 2025" min="2020" max="2099"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('modalFiscalYear') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Allocated Amount --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Allocated Amount (ZMW) <span class="text-red-500">*</span>
          </label>
          <input type="number" step="0.01" min="0" wire:model="allocatedAmount" placeholder="0.00"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('allocatedAmount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Read-only committed / spent when editing --}}
        @if($editingId && $this->editingAllocation)
        <div class="grid grid-cols-2 gap-4 pt-1">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Committed (ZMW)</label>
            <div class="w-full rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900 text-gray-600 dark:text-neutral-400 text-sm px-3 py-2">
              {{ number_format((float) $this->editingAllocation->committed_amount, 2) }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Spent (ZMW)</label>
            <div class="w-full rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900 text-gray-600 dark:text-neutral-400 text-sm px-3 py-2">
              {{ number_format((float) $this->editingAllocation->spent_amount, 2) }}
            </div>
          </div>
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
          <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update Allocation' : 'Create Allocation' }}</span>
          <span wire:loading wire:target="save">Saving…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

</div>
