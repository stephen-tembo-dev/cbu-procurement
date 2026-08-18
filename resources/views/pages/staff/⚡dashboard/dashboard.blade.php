<div>
  {{-- Page Header --}}
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
        Welcome back, {{ auth()->user()->name }}
      </p>
    </div>
    @can('pr.create')
    <a href="{{ route('requisitions.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-emerald-500/20">
      <ph-plus weight="bold" class="text-base"></ph-plus>
      New Requisition
    </a>
    @endcan
  </div>

  {{-- Stat Cards --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
          <ph-clipboard-text weight="fill" class="text-xl text-blue-600 dark:text-blue-400"></ph-clipboard-text>
        </div>
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Total</span>
      </div>
      <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $this->stats['total'] }}</p>
      <p class="text-xs text-gray-500 dark:text-neutral-500 mt-1">Requisitions</p>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center">
          <ph-clock weight="fill" class="text-xl text-amber-600 dark:text-amber-400"></ph-clock>
        </div>
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">In Progress</span>
      </div>
      <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $this->stats['active'] }}</p>
      <p class="text-xs text-gray-500 dark:text-neutral-500 mt-1">Awaiting action</p>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
          <ph-check-circle weight="fill" class="text-xl text-emerald-600 dark:text-emerald-400"></ph-check-circle>
        </div>
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Completed</span>
      </div>
      <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $this->stats['completed'] }}</p>
      <p class="text-xs text-gray-500 dark:text-neutral-500 mt-1">Paid & delivered</p>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center">
          <ph-x-circle weight="fill" class="text-xl text-red-600 dark:text-red-400"></ph-x-circle>
        </div>
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Rejected</span>
      </div>
      <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $this->stats['rejected'] }}</p>
      <p class="text-xs text-gray-500 dark:text-neutral-500 mt-1">Rejected or suspended</p>
    </div>
  </div>

  {{-- Inbox Table --}}
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center gap-3">
      <ph-tray weight="fill" class="text-xl text-gray-500 dark:text-neutral-400"></ph-tray>
      <h2 class="text-base font-semibold text-gray-900 dark:text-white">Action Inbox</h2>
    </div>
    <div class="p-2">
      {{ $this->table }}
    </div>
  </div>
</div>
