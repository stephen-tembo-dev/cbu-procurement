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
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Departments</h1>
      <div class="flex flex-wrap gap-1 mt-2">
        @foreach([['admin.users','Users'],['admin.departments','Departments'],['admin.suppliers','Suppliers'],['admin.stock-items','Stock Items'],['admin.budget','Budget']] as [$route,$label])
        <a href="{{ route($route) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-colors {{ $activeSection === last(explode('.',$route)) ? 'bg-emerald-500 text-white' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">{{ $label }}</a>
        @endforeach
      </div>
    </div>
    <button wire:click="openCreateDept"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-emerald-500/20 flex-shrink-0">
      <ph-plus weight="bold" class="text-base"></ph-plus>
      Add Department
    </button>
  </div>

  {{-- Table --}}
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700">
    <div class="p-2">
      {{ $this->table }}
    </div>
  </div>

  {{-- Department Modal --}}
  @if($showDeptModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.closeDeptModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDeptModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-lg p-6">

      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ $editingDeptId ? 'Edit Department' : 'Add Department' }}
        </h2>
        <button wire:click="closeDeptModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 transition-colors">
          <ph-x weight="bold" class="text-lg"></ph-x>
        </button>
      </div>

      <div class="space-y-4">

        {{-- Name --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Name <span class="text-red-500">*</span>
          </label>
          <input type="text" wire:model="deptName" placeholder="Department name"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('deptName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Code --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Code <span class="text-red-500">*</span>
          </label>
          <input type="text" wire:model="deptCode" placeholder="e.g. IT"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent uppercase">
          @error('deptCode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- HOD --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Head of Department (HOD)
          </label>
          <select wire:model="deptHodId"
                  class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="0">— None —</option>
            @foreach($this->usersForHod as $uid => $uname)
            <option value="{{ $uid }}">{{ $uname }}</option>
            @endforeach
          </select>
          @error('deptHodId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Is Active --}}
        <div>
          <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input type="checkbox" wire:model="deptIsActive"
                   class="w-4 h-4 rounded border-gray-300 dark:border-neutral-600 text-emerald-500 focus:ring-emerald-500">
            <span class="text-sm font-medium text-gray-700 dark:text-neutral-300">Is Active</span>
          </label>
        </div>

      </div>

      {{-- Actions --}}
      <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-gray-200 dark:border-neutral-700">
        <button wire:click="closeDeptModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="saveDept"
                class="px-5 py-2 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors"
                wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="saveDept">{{ $editingDeptId ? 'Update Department' : 'Create Department' }}</span>
          <span wire:loading wire:target="saveDept">Saving…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

  {{-- Cost Centres Modal --}}
  @if($showCcModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.closeCcModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeCcModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-xl p-6">

      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ $this->managingDept?->name ?? 'Department' }} &mdash; Cost Centres
        </h2>
        <button wire:click="closeCcModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 transition-colors">
          <ph-x weight="bold" class="text-lg"></ph-x>
        </button>
      </div>

      {{-- Existing Cost Centres --}}
      <div class="mb-5">
        @forelse($this->costCentresForDept as $cc)
        <div class="flex items-center justify-between gap-3 py-2.5 border-b border-gray-100 dark:border-neutral-700 last:border-0">
          <div class="flex items-center gap-3">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-neutral-300 font-mono">
              {{ $cc->code }}
            </span>
            <span class="text-sm text-gray-800 dark:text-neutral-200">{{ $cc->name }}</span>
          </div>
          <button wire:click="toggleCostCentreActive({{ $cc->id }})"
                  class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-lg transition-colors {{ $cc->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40' : 'bg-red-50 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40' }}">
            @if($cc->is_active)
              <ph-check-circle weight="fill" class="text-sm"></ph-check-circle>
              Active
            @else
              <ph-x-circle weight="fill" class="text-sm"></ph-x-circle>
              Inactive
            @endif
          </button>
        </div>
        @empty
        <p class="text-sm text-gray-500 dark:text-neutral-400 py-3 text-center">No cost centres yet.</p>
        @endforelse
      </div>

      {{-- Divider --}}
      <div class="relative my-5">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-200 dark:border-neutral-700"></div>
        </div>
        <div class="relative flex justify-center">
          <span class="bg-white dark:bg-neutral-800 px-3 text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">
            Add Cost Centre
          </span>
        </div>
      </div>

      {{-- Add Cost Centre Form --}}
      <div class="space-y-3">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">
              Code <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model="ccCode" placeholder="e.g. CC001"
                   class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent uppercase">
            @error('ccCode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 dark:text-neutral-300 mb-1">
              Name <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model="ccName" placeholder="Cost centre name"
                   class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            @error('ccName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="flex items-center justify-between gap-3 pt-1">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" wire:model="ccIsActive"
                   class="w-4 h-4 rounded border-gray-300 dark:border-neutral-600 text-emerald-500 focus:ring-emerald-500">
            <span class="text-xs font-medium text-gray-700 dark:text-neutral-300">Is Active</span>
          </label>
          <div class="flex items-center gap-2">
            <button wire:click="closeCcModal"
                    class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
              Close
            </button>
            <button wire:click="saveCostCentre"
                    class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors"
                    wire:loading.attr="disabled">
              <span wire:loading.remove wire:target="saveCostCentre">Save Cost Centre</span>
              <span wire:loading wire:target="saveCostCentre">Saving…</span>
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
  @endif

</div>
