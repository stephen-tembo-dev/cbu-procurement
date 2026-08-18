<div>
  {{-- Page Header --}}
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Purchase Requisitions</h1>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
        Browse and track all purchase requisitions
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

  {{-- Table --}}
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700">
    <div class="p-2">
      {{ $this->table }}
    </div>
  </div>
</div>
