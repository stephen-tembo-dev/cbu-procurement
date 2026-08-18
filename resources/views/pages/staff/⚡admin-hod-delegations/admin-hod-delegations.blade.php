<div>

  {{-- Flash --}}
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
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">HOD Delegations</h1>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
        Grant a colleague temporary authority to approve requisitions on your behalf while you are away.
      </p>
    </div>
    <button wire:click="openCreate"
      class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-xl transition-colors flex-shrink-0">
      <ph-plus weight="bold"></ph-plus>
      New Delegation
    </button>
  </div>

  {{-- Info callout --}}
  <div class="mb-5 flex gap-3 items-start bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl px-4 py-3">
    <ph-info weight="fill" class="text-amber-500 text-xl flex-shrink-0 mt-0.5"></ph-info>
    <p class="text-sm text-amber-800 dark:text-amber-200 leading-relaxed">
      An active delegation allows the named delegate to approve or reject purchase requisitions from your department during the specified dates.
      Creating a new delegation automatically revokes any existing active delegation for the same department.
      All actions taken by a delegate are recorded in the PR audit trail, clearly marked as delegated.
    </p>
  </div>

  {{-- Table --}}
  {{ $this->table }}

  {{-- Create modal --}}
  @if($showModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.closeModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-lg p-6">

      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
        <ph-git-branch weight="fill" class="text-emerald-500 text-xl"></ph-git-branch>
        New HOD Delegation
      </h3>

      <div class="space-y-4">

        {{-- Department (admin only - HODs see their dept pre-selected) --}}
        @if(auth()->user()->hasRole('admin') && !auth()->user()->hasRole('hod') || count($this->departments) > 1)
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Department <span class="text-red-500">*</span>
          </label>
          <select wire:model="departmentId"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">Select department…</option>
            @foreach($this->departments as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
          @error('departmentId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        @else
        <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-neutral-700 text-sm text-gray-700 dark:text-neutral-300 border border-gray-200 dark:border-neutral-600">
          <span class="font-medium">Department:</span>
          {{ collect($this->departments)->first() ?? 'Your department' }}
        </div>
        @endif

        {{-- Delegate --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Delegate to <span class="text-red-500">*</span>
          </label>
          <select wire:model="delegateeId"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="">Select a colleague…</option>
            @foreach($this->users as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
          @error('delegateeId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Date range --}}
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
              From <span class="text-red-500">*</span>
            </label>
            <input type="date" wire:model="startsAt"
              class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            @error('startsAt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
              To <span class="text-red-500">*</span>
            </label>
            <input type="date" wire:model="endsAt"
              class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            @error('endsAt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Reason --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Reason <span class="text-xs text-gray-400">(optional)</span>
          </label>
          <textarea wire:model="reason" rows="2" placeholder="e.g. Annual leave, conference travel…"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"></textarea>
          @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

      </div>

      {{-- Actions --}}
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" wire:click="closeModal"
          class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-neutral-400 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
          Cancel
        </button>
        <button type="button" wire:click="saveDelegation" wire:loading.attr="disabled"
          class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
          <span wire:loading.remove wire:target="saveDelegation">Create Delegation</span>
          <span wire:loading wire:target="saveDelegation">Saving…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

</div>
