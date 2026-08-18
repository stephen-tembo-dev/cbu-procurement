@php
    $summary    = $this->summary;
    $byDept     = $this->byDepartment;
    $allocations = $this->allocations;
    $totalAlloc = (float) $summary['allocated'];
@endphp

<div>

  {{-- Page header --}}
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Budget Dashboard</h1>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
        Budget allocation and utilisation across all departments and cost centres.
      </p>
    </div>

    {{-- Fiscal year selector --}}
    <div class="flex items-center gap-2">
      <label class="text-sm font-medium text-gray-600 dark:text-neutral-400">Fiscal Year</label>
      <select wire:model.live="fiscalYear"
        class="rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        @foreach($this->availableYears() as $yr)
        <option value="{{ $yr }}">{{ $yr }}</option>
        @endforeach
      </select>
    </div>
  </div>

  {{-- Summary stat cards --}}
  @php
    $usedPct = $totalAlloc > 0 ? min(100, round(((float)$summary['committed'] + (float)$summary['spent']) / $totalAlloc * 100)) : 0;
  @endphp
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center flex-shrink-0">
          <ph-wallet weight="fill" class="text-xl text-blue-600 dark:text-blue-400"></ph-wallet>
        </div>
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Total Allocated</span>
      </div>
      <p class="text-xl font-bold text-gray-900 dark:text-white">ZMW {{ number_format($summary['allocated'], 0) }}</p>
      <p class="text-xs text-gray-500 dark:text-neutral-500 mt-1">{{ $summary['count'] }} cost centre(s)</p>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center flex-shrink-0">
          <ph-lock-key weight="fill" class="text-xl text-amber-600 dark:text-amber-400"></ph-lock-key>
        </div>
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Committed</span>
      </div>
      <p class="text-xl font-bold text-gray-900 dark:text-white">ZMW {{ number_format($summary['committed'], 0) }}</p>
      <p class="text-xs text-gray-500 dark:text-neutral-500 mt-1">Reserved, awaiting payment</p>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center flex-shrink-0">
          <ph-receipt weight="fill" class="text-xl text-red-600 dark:text-red-400"></ph-receipt>
        </div>
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Spent</span>
      </div>
      <p class="text-xl font-bold text-gray-900 dark:text-white">ZMW {{ number_format($summary['spent'], 0) }}</p>
      <p class="text-xs text-gray-500 dark:text-neutral-500 mt-1">Payments confirmed</p>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-9 h-9 rounded-lg {{ $summary['available'] < 0 ? 'bg-red-50 dark:bg-red-500/10' : 'bg-emerald-50 dark:bg-emerald-500/10' }} flex items-center justify-center flex-shrink-0">
          <ph-piggy-bank weight="fill" class="text-xl {{ $summary['available'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}"></ph-piggy-bank>
        </div>
        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">Available</span>
      </div>
      <p class="text-xl font-bold {{ $summary['available'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
        ZMW {{ number_format($summary['available'], 0) }}
      </p>
      <p class="text-xs text-gray-500 dark:text-neutral-500 mt-1">Uncommitted balance</p>
    </div>

  </div>

  {{-- Overall utilisation bar --}}
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5 mb-6">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Overall Budget Utilisation — FY {{ $fiscalYear }}</h2>
      <span class="text-sm font-bold {{ $usedPct >= 90 ? 'text-red-600 dark:text-red-400' : ($usedPct >= 70 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
        {{ $usedPct }}%
      </span>
    </div>
    <div class="w-full bg-gray-200 dark:bg-neutral-700 rounded-full h-3 overflow-hidden">
      {{-- Spent portion --}}
      @php $spentPct = $totalAlloc > 0 ? min(100, round((float)$summary['spent'] / $totalAlloc * 100)) : 0; @endphp
      <div class="h-3 flex">
        <div class="h-3 bg-red-500 dark:bg-red-500 transition-all duration-500" style="width: {{ $spentPct }}%" title="Spent {{ $spentPct }}%"></div>
        <div class="h-3 bg-amber-400 dark:bg-amber-500 transition-all duration-500" style="width: {{ max(0, $usedPct - $spentPct) }}%" title="Committed {{ max(0, $usedPct - $spentPct) }}%"></div>
      </div>
    </div>
    <div class="flex items-center gap-4 mt-2 text-xs text-gray-500 dark:text-neutral-400">
      <span class="flex items-center gap-1"><span class="inline-block w-2.5 h-2.5 rounded-sm bg-red-500"></span> Spent</span>
      <span class="flex items-center gap-1"><span class="inline-block w-2.5 h-2.5 rounded-sm bg-amber-400"></span> Committed</span>
      <span class="flex items-center gap-1"><span class="inline-block w-2.5 h-2.5 rounded-sm bg-gray-200 dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600"></span> Available</span>
    </div>
  </div>

  {{-- Department breakdown --}}
  @if($byDept->isNotEmpty())
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
      <h2 class="text-base font-semibold text-gray-900 dark:text-white">Department Budget Allocation</h2>
      <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">Aggregated across all cost centres per department</p>
    </div>
    <div class="p-6 space-y-5">
      @foreach($byDept as $dept)
      @php
        $dAlloc     = (float) $dept['allocated'];
        $dCommitted = (float) $dept['committed'];
        $dSpent     = (float) $dept['spent'];
        $dAvail     = (float) $dept['available'];
        $dUsedPct   = $dAlloc > 0 ? min(100, round(($dCommitted + $dSpent) / $dAlloc * 100)) : 0;
        $dSpentPct  = $dAlloc > 0 ? min(100, round($dSpent / $dAlloc * 100)) : 0;
        $dCommPct   = max(0, $dUsedPct - $dSpentPct);
      @endphp
      <div>
        <div class="flex items-start justify-between mb-1.5">
          <div>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $dept['department'] }}</span>
            <span class="ml-2 text-xs text-gray-400 dark:text-neutral-500">{{ $dept['centres'] }} cost centre(s)</span>
          </div>
          <div class="text-right text-xs">
            <span class="font-semibold text-gray-700 dark:text-neutral-200">ZMW {{ number_format($dAlloc, 0) }}</span>
            <span class="text-gray-400 dark:text-neutral-500 ml-1">allocated</span>
          </div>
        </div>
        <div class="w-full bg-gray-200 dark:bg-neutral-700 rounded-full h-2.5 overflow-hidden">
          <div class="h-2.5 flex">
            <div class="h-2.5 bg-red-500 transition-all duration-500" style="width: {{ $dSpentPct }}%"></div>
            <div class="h-2.5 bg-amber-400 transition-all duration-500" style="width: {{ $dCommPct }}%"></div>
          </div>
        </div>
        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500 dark:text-neutral-400">
          <span>{{ $dUsedPct }}% used</span>
          <span class="text-red-500">Spent: ZMW {{ number_format($dSpent, 0) }}</span>
          <span class="text-amber-500">Committed: ZMW {{ number_format($dCommitted, 0) }}</span>
          <span class="{{ $dAvail < 0 ? 'text-red-600 font-semibold' : 'text-emerald-600' }}">Available: ZMW {{ number_format($dAvail, 0) }}</span>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Cost centre detail table --}}
  <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
      <h2 class="text-base font-semibold text-gray-900 dark:text-white">Cost Centre Breakdown</h2>
      <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
        <span class="inline-flex items-center gap-1 text-amber-600 dark:text-amber-400 font-medium">
          <ph-lock-key weight="fill" class="text-xs"></ph-lock-key> Committed
        </span>
        = funds reserved by Bursar for approved requisitions awaiting payment.
        <span class="ml-2 inline-flex items-center gap-1 text-red-600 dark:text-red-400 font-medium">
          <ph-receipt weight="fill" class="text-xs"></ph-receipt> Spent
        </span>
        = payments already confirmed and disbursed.
      </p>
    </div>

    @if($allocations->isEmpty())
    <div class="px-6 py-10 text-center">
      <ph-chart-bar weight="regular" class="text-4xl text-gray-300 dark:text-neutral-600 mx-auto mb-2"></ph-chart-bar>
      <p class="text-sm text-gray-500 dark:text-neutral-400">No budget allocations for FY {{ $fiscalYear }}.</p>
      @if(auth()->user()->hasRole('admin'))
      <a href="{{ route('admin.budget') }}"
         class="mt-3 inline-flex items-center gap-1.5 text-sm text-emerald-600 dark:text-emerald-400 hover:underline">
        <ph-plus weight="bold" class="text-sm"></ph-plus>
        Set up allocations in Admin → Budget
      </a>
      @endif
    </div>
    @else
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-neutral-700/50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Cost Centre</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Department</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Allocated</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Committed</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider">Spent</th>
            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Available</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider w-36">Utilisation</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
          @foreach($allocations as $alloc)
          @php
            $ccAlloc    = (float) $alloc->allocated_amount;
            $ccComm     = (float) $alloc->committed_amount;
            $ccSpent    = (float) $alloc->spent_amount;
            $ccAvail    = $alloc->availableBalance();
            $ccUsedPct  = $ccAlloc > 0 ? min(100, round(($ccComm + $ccSpent) / $ccAlloc * 100)) : 0;
            $ccSpentPct = $ccAlloc > 0 ? min(100, round($ccSpent / $ccAlloc * 100)) : 0;
            $ccCommPct  = max(0, $ccUsedPct - $ccSpentPct);
          @endphp
          <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition-colors">
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-mono font-semibold bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-neutral-300">
                  {{ $alloc->costCentre->code }}
                </span>
                <span class="text-gray-900 dark:text-white">{{ $alloc->costCentre->name }}</span>
              </div>
            </td>
            <td class="px-4 py-3 text-gray-500 dark:text-neutral-400">
              {{ $alloc->costCentre->department->name ?? '—' }}
            </td>
            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
              {{ number_format($ccAlloc, 2) }}
            </td>
            <td class="px-4 py-3 text-right font-medium text-amber-600 dark:text-amber-400">
              {{ number_format($ccComm, 2) }}
            </td>
            <td class="px-4 py-3 text-right font-medium text-red-600 dark:text-red-400">
              {{ number_format($ccSpent, 2) }}
            </td>
            <td class="px-4 py-3 text-right font-bold {{ $ccAvail < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
              {{ number_format($ccAvail, 2) }}
              @if($ccAvail < 0)
              <ph-warning weight="fill" class="inline text-xs ml-0.5"></ph-warning>
              @endif
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="flex-1 bg-gray-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                  <div class="h-2 flex">
                    <div class="h-2 bg-red-500" style="width: {{ $ccSpentPct }}%"></div>
                    <div class="h-2 bg-amber-400" style="width: {{ $ccCommPct }}%"></div>
                  </div>
                </div>
                <span class="text-xs font-medium {{ $ccUsedPct >= 90 ? 'text-red-600 dark:text-red-400' : ($ccUsedPct >= 70 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-neutral-400') }} whitespace-nowrap">
                  {{ $ccUsedPct }}%
                </span>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="bg-gray-50 dark:bg-neutral-700/50 border-t-2 border-gray-200 dark:border-neutral-600">
          <tr>
            <td colspan="2" class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-neutral-300">Totals</td>
            <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">{{ number_format($summary['allocated'], 2) }}</td>
            <td class="px-4 py-3 text-right text-sm font-bold text-amber-600 dark:text-amber-400">{{ number_format($summary['committed'], 2) }}</td>
            <td class="px-4 py-3 text-right text-sm font-bold text-red-600 dark:text-red-400">{{ number_format($summary['spent'], 2) }}</td>
            <td class="px-4 py-3 text-right text-sm font-bold {{ $summary['available'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
              {{ number_format($summary['available'], 2) }}
            </td>
            <td class="px-4 py-3">
              <span class="text-xs font-bold {{ $usedPct >= 90 ? 'text-red-600 dark:text-red-400' : ($usedPct >= 70 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                {{ $usedPct }}% used
              </span>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    @endif
  </div>

</div>
