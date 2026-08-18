<div x-on:open-export-url.window="window.open($event.detail.url, '_blank')">

  {{-- Page Header --}}
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reports</h1>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">Procurement analytics and operational reporting.</p>
    </div>
    <div class="flex items-center gap-2">
      <button
        wire:click="exportExcel"
        wire:loading.attr="disabled"
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors disabled:opacity-50"
      >
        <ph-file-xls weight="regular" class="text-base text-emerald-600"></ph-file-xls>
        <span wire:loading.remove wire:target="exportExcel">Excel</span>
        <span wire:loading wire:target="exportExcel">Exporting…</span>
      </button>
      <button
        wire:click="exportPdf"
        wire:loading.attr="disabled"
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors disabled:opacity-50"
      >
        <ph-file-pdf weight="regular" class="text-base text-red-500"></ph-file-pdf>
        <span wire:loading.remove wire:target="exportPdf">PDF</span>
        <span wire:loading wire:target="exportPdf">Generating…</span>
      </button>
    </div>
  </div>

  {{-- Tab Navigation --}}
  @php
    $tabs = [
      'approved_orders'  => ['label' => 'Approved Orders',      'icon' => 'ph-check-square'],
      'paid_orders'      => ['label' => 'Paid Orders',           'icon' => 'ph-currency-dollar'],
      'pending_payments' => ['label' => 'Pending Payments',      'icon' => 'ph-clock'],
      'commitment'       => ['label' => 'Budget Commitment',     'icon' => 'ph-piggy-bank'],
      'undelivered'      => ['label' => 'Undelivered Orders',    'icon' => 'ph-truck'],
      'compliance'       => ['label' => 'Compliance Exceptions', 'icon' => 'ph-warning'],
    ];
  @endphp

  <div class="mb-5 flex flex-wrap gap-1 bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-1.5">
    @foreach($tabs as $key => $tab)
    <button
      wire:click="switchReport('{{ $key }}')"
      class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap
             {{ $activeReport === $key
                ? 'bg-emerald-500 text-white shadow-sm'
                : 'text-gray-600 dark:text-neutral-400 hover:bg-gray-100 dark:hover:bg-neutral-700' }}"
    >
      <{{ $tab['icon'] }} weight="{{ $activeReport === $key ? 'fill' : 'regular' }}" class="text-base"></{{ $tab['icon'] }}>
      {{ $tab['label'] }}
    </button>
    @endforeach
  </div>

  {{-- Date Range Filter (for date-filtered reports) --}}
  @if(in_array($activeReport, ['approved_orders', 'paid_orders', 'pending_payments']))
  <div class="mb-4 flex flex-wrap items-end gap-3 bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 px-5 py-4">
    <div>
      <label class="block text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">From</label>
      <input type="date" wire:model="dateFrom"
             class="rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 text-sm text-gray-900 dark:text-white px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">To</label>
      <input type="date" wire:model="dateTo"
             class="rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 text-sm text-gray-900 dark:text-white px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
    </div>
    <button wire:click="applyDates"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition-colors">
      <ph-funnel weight="fill" class="text-base"></ph-funnel>
      Apply
    </button>
    @if($dateFrom || $dateTo)
    <button wire:click="$set('dateFrom',''); $set('dateTo','')"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 dark:text-neutral-400 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
      <ph-x weight="bold" class="text-base"></ph-x>
      Clear
    </button>
    @endif
    @if($dateFrom || $dateTo)
    <p class="text-xs text-gray-500 dark:text-neutral-500 ml-1">
      Showing:
      {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : '—' }}
      →
      {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : '—' }}
    </p>
    @endif
  </div>
  @endif

  {{-- Fiscal Year Filter (for budget commitment) --}}
  @if($activeReport === 'commitment')
  <div class="mb-4 flex items-end gap-3 bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 px-5 py-4">
    <div>
      <label class="block text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">Fiscal Year</label>
      <input type="number" wire:model.live="fiscalYear" min="2020" max="2099"
             class="w-28 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 text-sm text-gray-900 dark:text-white px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none" />
    </div>
    <p class="text-xs text-gray-500 dark:text-neutral-500 pb-2">Budget allocation breakdown for FY {{ $fiscalYear }}.</p>
  </div>
  @endif

  {{-- Report Table Card --}}
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center gap-3">
      @php
        $icons = [
          'approved_orders'  => 'ph-check-square',
          'paid_orders'      => 'ph-currency-dollar',
          'pending_payments' => 'ph-clock',
          'commitment'       => 'ph-piggy-bank',
          'undelivered'      => 'ph-truck',
          'compliance'       => 'ph-warning',
        ];
        $descriptions = [
          'approved_orders'  => 'Issued purchase orders awaiting delivery.',
          'paid_orders'      => 'Purchase orders confirmed as fully paid.',
          'pending_payments' => 'Payments initiated but not yet confirmed.',
          'commitment'       => 'Budget allocation vs committed vs spent by cost centre.',
          'undelivered'      => 'Issued orders where goods or services have not been received.',
          'compliance'       => 'Requisitions currently in procurement or audit review.',
        ];
      @endphp
      <{{ $icons[$activeReport] }} weight="fill" class="text-xl text-gray-500 dark:text-neutral-400"></{{ $icons[$activeReport] }}>
      <div>
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $tabs[$activeReport]['label'] }}</h2>
        <p class="text-xs text-gray-500 dark:text-neutral-500 mt-0.5">{{ $descriptions[$activeReport] }}</p>
      </div>
    </div>
    <div class="p-2">
      {{ $this->table }}
    </div>
  </div>

</div>
