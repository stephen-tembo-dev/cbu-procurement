<div>

  {{-- Flash --}}
  @if($flash)
  <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-xl border {{ $flashType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-300' : 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300' }}">
    <ph-check-circle weight="fill" class="text-xl flex-shrink-0 mt-0.5"></ph-check-circle>
    <span class="text-sm flex-1">{{ $flash }}</span>
    <button wire:click="dismissFlash" class="opacity-60 hover:opacity-100"><ph-x weight="bold" class="text-sm"></ph-x></button>
  </div>
  @endif

  {{-- Header --}}
  <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Catalog Settings</h1>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">Define the categories and units of measure used across all purchase forms.</p>
    </div>

    {{-- Tab add button --}}
    @if($activeTab === 'categories')
    <button wire:click="openCreateCategory"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-emerald-500/20 flex-shrink-0">
      <ph-plus weight="bold" class="text-base"></ph-plus>
      Add Category
    </button>
    @else
    <button wire:click="openCreateUnit"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-emerald-500/20 flex-shrink-0">
      <ph-plus weight="bold" class="text-base"></ph-plus>
      Add Unit
    </button>
    @endif
  </div>

  {{-- Admin nav tabs --}}
  <div class="flex flex-wrap gap-1 mb-5">
    @foreach([['admin.users','Users'],['admin.departments','Departments'],['admin.suppliers','Suppliers'],['admin.stock-items','Stock Items'],['admin.budget','Budget'],['admin.catalog','Catalog']] as [$route,$label])
    <a href="{{ route($route) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs($route) ? 'bg-emerald-500 text-white' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">{{ $label }}</a>
    @endforeach
  </div>

  {{-- Tab switcher --}}
  <div class="flex gap-1 bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-1.5 mb-5 w-fit">
    <button wire:click="$set('activeTab','categories')"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors
                   {{ $activeTab === 'categories' ? 'bg-emerald-500 text-white shadow-sm' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">
      <ph-tag weight="{{ $activeTab === 'categories' ? 'fill' : 'regular' }}" class="text-base"></ph-tag>
      Item Categories
      <span class="px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'categories' ? 'bg-white/20' : 'bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-neutral-300' }}">
        {{ $this->categories->count() }}
      </span>
    </button>
    <button wire:click="$set('activeTab','units')"
            class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium transition-colors
                   {{ $activeTab === 'units' ? 'bg-emerald-500 text-white shadow-sm' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">
      <ph-ruler weight="{{ $activeTab === 'units' ? 'fill' : 'regular' }}" class="text-base"></ph-ruler>
      Units of Measure
      <span class="px-1.5 py-0.5 text-xs rounded-full {{ $activeTab === 'units' ? 'bg-white/20' : 'bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-neutral-300' }}">
        {{ $this->units->count() }}
      </span>
    </button>
  </div>

  {{-- ── CATEGORIES TAB ─────────────────────────────────────────────────── --}}
  @if($activeTab === 'categories')
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
      <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Item Categories</h2>
      <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
        Categories appear as a dropdown in requisition forms. Inactive categories are hidden from users but kept for historical records.
      </p>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 dark:bg-neutral-700/50">
        <tr>
          <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Category Name</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider hidden sm:table-cell">Description</th>
          <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider w-24">Status</th>
          <th class="px-5 py-3 w-24"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
        @forelse($this->categories as $cat)
        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition-colors {{ ! $cat->is_active ? 'opacity-50' : '' }}">
          <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $cat->name }}</td>
          <td class="px-5 py-3 text-gray-500 dark:text-neutral-400 hidden sm:table-cell">{{ $cat->description ?? '—' }}</td>
          <td class="px-5 py-3 text-center">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $cat->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-neutral-400' }}">
              {{ $cat->is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="px-5 py-3">
            <div class="flex items-center justify-end gap-2">
              <button wire:click="openEditCategory({{ $cat->id }})"
                      class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 transition-colors"
                      title="Edit">
                <ph-pencil weight="bold" class="text-sm"></ph-pencil>
              </button>
              <button wire:click="toggleCategory({{ $cat->id }})"
                      class="p-1.5 {{ $cat->is_active ? 'text-amber-400 hover:text-amber-600' : 'text-emerald-400 hover:text-emerald-600' }} transition-colors"
                      title="{{ $cat->is_active ? 'Deactivate' : 'Activate' }}">
                <ph-power weight="bold" class="text-sm"></ph-power>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-neutral-400">No categories yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @endif

  {{-- ── UNITS TAB ───────────────────────────────────────────────────────── --}}
  @if($activeTab === 'units')
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
      <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Units of Measure</h2>
      <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
        Units appear as a dropdown in requisition and purchase order forms. The abbreviation (e.g. "pcs") is what gets stored and printed on documents.
      </p>
    </div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 dark:bg-neutral-700/50">
        <tr>
          <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Name</th>
          <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider w-24">Abbreviation</th>
          <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider w-24">Status</th>
          <th class="px-5 py-3 w-24"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
        @forelse($this->units as $unit)
        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition-colors {{ ! $unit->is_active ? 'opacity-50' : '' }}">
          <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $unit->name }}</td>
          <td class="px-5 py-3">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-gray-100 dark:bg-neutral-700 text-gray-700 dark:text-neutral-300">
              {{ $unit->abbreviation }}
            </span>
          </td>
          <td class="px-5 py-3 text-center">
            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $unit->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-neutral-400' }}">
              {{ $unit->is_active ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="px-5 py-3">
            <div class="flex items-center justify-end gap-2">
              <button wire:click="openEditUnit({{ $unit->id }})"
                      class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 transition-colors"
                      title="Edit">
                <ph-pencil weight="bold" class="text-sm"></ph-pencil>
              </button>
              <button wire:click="toggleUnit({{ $unit->id }})"
                      class="p-1.5 {{ $unit->is_active ? 'text-amber-400 hover:text-amber-600' : 'text-emerald-400 hover:text-emerald-600' }} transition-colors"
                      title="{{ $unit->is_active ? 'Deactivate' : 'Activate' }}">
                <ph-power weight="bold" class="text-sm"></ph-power>
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-neutral-400">No units yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @endif

  {{-- ================================================================
       Category Modal
       ================================================================ --}}
  @if($showCategoryModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.closeCategoryModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeCategoryModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-md p-6">

      <div class="flex items-center justify-between mb-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ $editingCategoryId ? 'Edit Category' : 'Add Category' }}
        </h3>
        <button wire:click="closeCategoryModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200">
          <ph-x weight="bold" class="text-lg"></ph-x>
        </button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Name <span class="text-red-500">*</span>
          </label>
          <input type="text" wire:model="categoryName" placeholder="e.g. Stationery & Office Supplies"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('categoryName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Description <span class="text-gray-400 font-normal">(optional)</span>
          </label>
          <input type="text" wire:model="categoryDescription" placeholder="Brief description shown to users"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('categoryDescription') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer select-none">
          <input type="checkbox" wire:model="categoryActive"
                 class="w-4 h-4 rounded border-gray-300 dark:border-neutral-600 text-emerald-500 focus:ring-emerald-500">
          <span class="text-sm font-medium text-gray-700 dark:text-neutral-300">Active (visible to users)</span>
        </label>
      </div>

      <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-gray-200 dark:border-neutral-700">
        <button wire:click="closeCategoryModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="saveCategory"
                class="px-5 py-2 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors"
                wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="saveCategory">{{ $editingCategoryId ? 'Update' : 'Create' }}</span>
          <span wire:loading wire:target="saveCategory">Saving…</span>
        </button>
      </div>
    </div>
  </div>
  @endif

  {{-- ================================================================
       Unit of Measure Modal
       ================================================================ --}}
  @if($showUnitModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.closeUnitModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeUnitModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-md p-6">

      <div class="flex items-center justify-between mb-5">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ $editingUnitId ? 'Edit Unit of Measure' : 'Add Unit of Measure' }}
        </h3>
        <button wire:click="closeUnitModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200">
          <ph-x weight="bold" class="text-lg"></ph-x>
        </button>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Full Name <span class="text-red-500">*</span>
          </label>
          <input type="text" wire:model="unitName" placeholder="e.g. Pieces"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('unitName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Abbreviation <span class="text-red-500">*</span>
          </label>
          <input type="text" wire:model="unitAbbreviation" placeholder="e.g. pcs"
                 class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">This is what gets stored on documents (e.g. "pcs", "kg", "hrs").</p>
          @error('unitAbbreviation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer select-none">
          <input type="checkbox" wire:model="unitActive"
                 class="w-4 h-4 rounded border-gray-300 dark:border-neutral-600 text-emerald-500 focus:ring-emerald-500">
          <span class="text-sm font-medium text-gray-700 dark:text-neutral-300">Active (visible to users)</span>
        </label>
      </div>

      <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-gray-200 dark:border-neutral-700">
        <button wire:click="closeUnitModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="saveUnit"
                class="px-5 py-2 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors"
                wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="saveUnit">{{ $editingUnitId ? 'Update' : 'Create' }}</span>
          <span wire:loading wire:target="saveUnit">Saving…</span>
        </button>
      </div>
    </div>
  </div>
  @endif

</div>
