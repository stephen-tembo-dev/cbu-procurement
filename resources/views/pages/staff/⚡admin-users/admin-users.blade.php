<div>

  {{-- Flash message --}}
  @if($flash)
  <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-xl border {{ $flashType === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-300' : 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300' }}">
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
  <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
      <div class="flex flex-wrap gap-1 mt-2">
        @foreach([['admin.users','Users'],['admin.departments','Departments'],['admin.suppliers','Suppliers'],['admin.stock-items','Stock Items'],['admin.budget','Budget']] as [$route,$label])
        <a href="{{ route($route) }}" class="px-3 py-1 rounded-lg text-xs font-medium transition-colors {{ $activeSection === last(explode('.',$route)) ? 'bg-emerald-500 text-white' : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}">{{ $label }}</a>
        @endforeach
      </div>
    </div>
    <button wire:click="openCreate"
      class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors flex-shrink-0">
      <ph-plus weight="bold"></ph-plus>
      Add User
    </button>
  </div>

  {{-- Filament Table --}}
  {{ $this->table }}

  {{-- Create / Edit Modal --}}
  @if($showModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.closeModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-lg p-6">

      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5">
        {{ $editingId ? 'Edit User' : 'Add User' }}
      </h3>

      <div class="space-y-4">

        {{-- Name --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Name <span class="text-red-500">*</span>
          </label>
          <input type="text" wire:model="name" placeholder="Full name"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Email --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Email <span class="text-red-500">*</span>
          </label>
          <input type="email" wire:model="email" placeholder="email@example.com"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Password (create only) --}}
        @if($editingId === null)
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Password <span class="text-red-500">*</span>
          </label>
          <input type="password" wire:model="password" placeholder="Min. 8 characters"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        @endif

        {{-- Department --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Department</label>
          <select wire:model="departmentId"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="0">— None —</option>
            @foreach($this->departments as $id => $deptName)
              <option value="{{ $id }}">{{ $deptName }}</option>
            @endforeach
          </select>
          @error('departmentId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Role --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Role <span class="text-red-500">*</span>
          </label>
          <select wire:model="role"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            @foreach(['requester' => 'Requester', 'hod' => 'Head of Department', 'stores' => 'Stores', 'bursar' => 'Bursar', 'vc' => 'Vice Chancellor', 'procurement' => 'Procurement', 'auditor' => 'Auditor', 'admin' => 'Admin'] as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
          @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Is Active --}}
        <div class="flex items-center gap-3">
          <input type="checkbox" wire:model="isActive" id="isActive"
            class="w-4 h-4 rounded border-gray-300 dark:border-neutral-600 text-emerald-500 focus:ring-emerald-500">
          <label for="isActive" class="text-sm font-medium text-gray-700 dark:text-neutral-300">Active</label>
        </div>

      </div>

      {{-- Modal actions --}}
      <div class="flex gap-3 justify-end mt-6">
        <button wire:click="closeModal"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="save"
          wire:loading.attr="disabled"
          class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-lg transition-colors disabled:opacity-60">
          <span wire:loading.remove wire:target="save">
            <ph-floppy-disk weight="bold" class="inline mr-1"></ph-floppy-disk>
            {{ $editingId ? 'Update User' : 'Create User' }}
          </span>
          <span wire:loading wire:target="save">Saving…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

</div>
