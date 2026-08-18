<div>

  {{-- Page header --}}
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Purchase Orders</h1>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">All purchase orders across requisitions.</p>
    </div>
    @if(auth()->user()->hasRole('procurement'))
    <a href="{{ route('requisitions.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-neutral-300 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
      <ph-list weight="bold"></ph-list>
      View Requisitions
    </a>
    @endif
  </div>

  {{ $this->table }}

</div>
