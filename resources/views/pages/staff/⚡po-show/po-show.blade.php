@php
    $po   = $this->po;
    $pr   = $po->purchaseRequisition;
    $user = auth()->user();

    $statusColors = [
        'draft'               => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        'issued'              => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'partially_delivered' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        'delivered'           => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    ];
    $statusBadge = $statusColors[$po->status] ?? 'bg-gray-100 text-gray-700';
    $statusLabel = ucwords(str_replace('_', ' ', $po->status));

    $showReceivedCols = in_array($po->status, ['issued', 'partially_delivered', 'delivered']);
@endphp

<div>

  {{-- Flash message --}}
  @if($flash)
  <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-xl border
    {{ $flashType === 'success'
        ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-300'
        : 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300' }}">
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

  {{-- ================================================================
       Stock Catalog Panel
       Shown after goods receipt when some PR items have no stock link.
       Stores can create a stock record for each, or skip.
       ================================================================ --}}
  @if($showCatalogPanel)
  <div class="mb-6 bg-white dark:bg-neutral-800 rounded-xl border-2 border-amber-300 dark:border-amber-600 overflow-hidden">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 px-6 py-4 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-700">
      <div class="flex items-start gap-3">
        <ph-warehouse weight="fill" class="text-2xl text-amber-500 flex-shrink-0 mt-0.5"></ph-warehouse>
        <div>
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">Stock Catalog — New Items Received</h3>
          <p class="text-sm text-gray-600 dark:text-neutral-400 mt-0.5">
            The items below have no stock record. They will be delivered directly to the requester with no stock movement.
            You can add them to the catalog now so future requests are tracked automatically — or skip and proceed.
          </p>
        </div>
      </div>
      <button wire:click="dismissCatalogPanel"
        class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 flex-shrink-0 mt-0.5">
        <ph-x weight="bold" class="text-lg"></ph-x>
      </button>
    </div>

    {{-- Per-item entries --}}
    <div class="divide-y divide-gray-100 dark:divide-neutral-700">
      @foreach($unlinkedPrItems as $prItem)
      @php $prItemId = $prItem['pr_item_id']; $entry = $catalogEntries[$prItemId] ?? []; @endphp
      <div class="px-6 py-5">

        {{-- Item header --}}
        <div class="flex items-start justify-between gap-4 mb-4">
          <div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $prItem['description'] }}</p>
            <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-neutral-400">
              @if($prItem['category'])
              <span class="inline-flex items-center gap-1">
                <ph-tag weight="fill"></ph-tag> {{ $prItem['category'] }}
              </span>
              @endif
              <span>Qty received: <strong class="text-gray-700 dark:text-neutral-200">{{ number_format($prItem['qty'], 2) }} {{ $prItem['unit'] }}</strong></span>
            </div>
          </div>
          {{-- Skip toggle --}}
          <label class="flex items-center gap-2 cursor-pointer flex-shrink-0">
            <input type="checkbox"
              wire:model.live="catalogEntries.{{ $prItemId }}.skip"
              class="rounded border-gray-300 dark:border-neutral-600 text-amber-500 focus:ring-amber-500">
            <span class="text-xs font-medium text-gray-600 dark:text-neutral-400">Skip — no catalog entry</span>
          </label>
        </div>

        @if(! ($entry['skip'] ?? false))
        {{-- Notice --}}
        <div class="flex items-start gap-2 px-3 py-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/60 mb-4 text-xs text-amber-800 dark:text-amber-300">
          <ph-info weight="fill" class="flex-shrink-0 mt-0.5"></ph-info>
          <span>Without a stock record this item is treated as a <strong>one-off delivery</strong>. Adding it to the catalog lets stores issue it and track quantity in future requisitions.</span>
        </div>

        {{-- Catalog entry form --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">
              Stock Code <span class="text-red-500">*</span>
            </label>
            <input type="text"
              wire:model="catalogEntries.{{ $prItemId }}.stock_code"
              placeholder="e.g. OFF-A4-001"
              class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent uppercase">
            @error("catalogEntries.{$prItemId}.stock_code")
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Reorder Level</label>
            <input type="number"
              wire:model="catalogEntries.{{ $prItemId }}.reorder_level"
              min="0" step="0.01" placeholder="0"
              class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
            @error("catalogEntries.{$prItemId}.reorder_level")
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Track in stock?</label>
            <div class="flex gap-4 mt-2">
              <label class="flex items-center gap-1.5 cursor-pointer text-sm">
                <input type="radio"
                  wire:model="catalogEntries.{{ $prItemId }}.is_stocked"
                  value="1"
                  class="text-amber-500 focus:ring-amber-500">
                <span class="text-gray-700 dark:text-neutral-300">Yes — add qty to stock</span>
              </label>
              <label class="flex items-center gap-1.5 cursor-pointer text-sm">
                <input type="radio"
                  wire:model="catalogEntries.{{ $prItemId }}.is_stocked"
                  value="0"
                  class="text-amber-500 focus:ring-amber-500">
                <span class="text-gray-700 dark:text-neutral-300">No — orderable only</span>
              </label>
            </div>
          </div>

        </div>
        @else
        <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-neutral-500 italic">
          <ph-arrow-right weight="bold"></ph-arrow-right>
          Skipped — this item will remain untracked in the catalog.
        </div>
        @endif

      </div>
      @endforeach
    </div>

    {{-- Footer actions --}}
    <div class="flex items-center justify-between gap-4 px-6 py-4 bg-gray-50 dark:bg-neutral-700/40 border-t border-gray-200 dark:border-neutral-700">
      <p class="text-xs text-gray-500 dark:text-neutral-400">
        Items you skip will not be added to the catalog. You can always add them later via the stock admin page.
      </p>
      <div class="flex gap-3 flex-shrink-0">
        <button wire:click="dismissCatalogPanel"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Skip All
        </button>
        <button wire:click="saveCatalogEntries"
          wire:loading.attr="disabled"
          class="px-5 py-2 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors">
          <span wire:loading.remove wire:target="saveCatalogEntries">
            <ph-stack-simple weight="bold" class="inline mr-1"></ph-stack-simple>
            Save to Catalog
          </span>
          <span wire:loading wire:target="saveCatalogEntries">Saving…</span>
        </button>
      </div>
    </div>

  </div>
  @endif

  {{-- Page header --}}
  <div class="mb-8 flex items-start gap-4">
    <a href="{{ route('po.index') }}"
       class="mt-1 w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors flex-shrink-0">
      <ph-arrow-left weight="bold" class="text-base"></ph-arrow-left>
    </a>
    <div>
      <div class="flex items-center gap-3 flex-wrap">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $po->po_number }}</h1>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusBadge }}">
          {{ $statusLabel }}
        </span>
      </div>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
        Created {{ $po->created_at->format('d M Y, H:i') }}
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: main details --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- PO Details --}}
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Order Details</h2>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Supplier</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $po->supplier->name }}</dd>
            @if($po->supplier->contact_person)
            <dd class="text-xs text-gray-400 dark:text-neutral-500">{{ $po->supplier->contact_person }}</dd>
            @endif
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Total Value</dt>
            <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
              ZMW {{ number_format($po->total_value, 2) }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Issued Date</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
              {{ $po->issued_date?->format('d M Y') ?? '—' }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Expected Delivery</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
              {{ $po->expected_delivery_date?->format('d M Y') ?? '—' }}
            </dd>
          </div>
          @if($po->actual_delivery_date)
          <div class="col-span-2">
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Actual Delivery</dt>
            <dd class="mt-1 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
              <ph-check-circle weight="fill" class="inline mr-1"></ph-check-circle>
              {{ $po->actual_delivery_date->format('d M Y') }}
            </dd>
          </div>
          @endif
        </dl>
      </div>

      {{-- Line Items --}}
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Line Items</h2>
          @if($showReceivedCols)
          <span class="text-xs text-gray-500 dark:text-neutral-400">Showing received quantities</span>
          @endif
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-neutral-700/50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Description</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Ordered</th>
                @if($showReceivedCols)
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Received</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Outstanding</th>
                @endif
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Unit</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Unit Price</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
              @foreach($po->items as $item)
              @php
                $received    = (float) ($item->quantity_received ?? 0);
                $ordered     = (float) $item->quantity;
                $outstanding = max(0, $ordered - $received);
                $fullyRecvd  = $received >= $ordered;
              @endphp
              <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition-colors">
                <td class="px-4 py-3 text-gray-900 dark:text-white">
                  {{ $item->description }}
                  @if($showReceivedCols && $item->received_date)
                  <span class="block text-xs text-gray-400 dark:text-neutral-500">Last receipt: {{ $item->received_date->format('d M Y') }}</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($ordered, 2) }}</td>
                @if($showReceivedCols)
                <td class="px-4 py-3 text-right font-semibold {{ $fullyRecvd ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400' }}">
                  {{ number_format($received, 2) }}
                  @if($fullyRecvd)
                  <ph-check-circle weight="fill" class="inline text-xs ml-0.5"></ph-check-circle>
                  @endif
                </td>
                <td class="px-4 py-3 text-right {{ $outstanding > 0 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-gray-400 dark:text-neutral-500' }}">
                  {{ $outstanding > 0 ? number_format($outstanding, 2) : '—' }}
                </td>
                @endif
                <td class="px-4 py-3 text-gray-500 dark:text-neutral-400">{{ $item->unit_of_measure ?? '—' }}</td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($item->unit_price, 2) }}</td>
                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                  {{ number_format($item->total_price, 2) }}
                </td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-neutral-700/50">
              <tr>
                <td colspan="{{ $showReceivedCols ? 6 : 4 }}" class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-neutral-300">Total</td>
                <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                  ZMW {{ number_format($po->total_value, 2) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- Payment info (if exists) --}}
      @if($po->payment)
      @php $payment = $po->payment; @endphp
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Payment Record</h2>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Amount</dt>
            <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
              ZMW {{ number_format($payment->amount, 2) }}
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Status</dt>
            <dd class="mt-1 text-sm font-medium capitalize text-gray-900 dark:text-white">
              {{ str_replace('_', ' ', $payment->status) }}
            </dd>
          </div>
          @if($payment->vcApprover)
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">VC Approved By</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->vcApprover->name }}</dd>
            @if($payment->vc_approved_at)
            <dd class="text-xs text-gray-400 dark:text-neutral-500">{{ $payment->vc_approved_at->format('d M Y') }}</dd>
            @endif
          </div>
          @endif
          @if($payment->bursarConfirmer)
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Confirmed By</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $payment->bursarConfirmer->name }}</dd>
            @if($payment->paid_at)
            <dd class="text-xs text-gray-400 dark:text-neutral-500">Paid {{ $payment->paid_at->format('d M Y') }}</dd>
            @endif
          </div>
          @endif
        </dl>
      </div>
      @endif

    </div>

    {{-- Right: context + actions --}}
    <div class="space-y-6">

      {{-- Action Panel --}}
      @php
        $canIssue   = $po->status === 'draft'
                      && $user->hasRole('procurement');
        $canReceive = in_array($po->status, ['issued', 'partially_delivered'])
                      && $user->hasRole('stores');
      @endphp

      @if($canIssue || $canReceive)
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
          <ph-lightning-a weight="fill" class="inline text-amber-500 mr-1"></ph-lightning-a>
          Your Action Required
        </h2>

        @if($canIssue)
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">
          This PO is in draft. Issue it to send to the supplier and formally record the order.
        </p>
        <button wire:click="issuePo"
          class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition-colors"
          wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="issuePo">
            <ph-paper-plane-tilt weight="bold" class="inline mr-1"></ph-paper-plane-tilt>
            Issue Purchase Order
          </span>
          <span wire:loading wire:target="issuePo">Issuing…</span>
        </button>
        @endif

        @if($canReceive)
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">
          Record the items delivered by the supplier against this Purchase Order. You can do this in multiple batches if not all arrive at once.
        </p>
        <button wire:click="openReceiveForm"
          class="w-full py-2.5 bg-teal-500 hover:bg-teal-600 text-white text-sm font-semibold rounded-lg transition-colors"
          wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="openReceiveForm">
            <ph-package-check weight="bold" class="inline mr-1"></ph-package-check>
            Enter Goods Receipt
          </span>
          <span wire:loading wire:target="openReceiveForm">Loading…</span>
        </button>
        @endif
      </div>
      @endif

      {{-- Linked Requisition --}}
      @if($pr)
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Linked Requisition</h2>
        <dl class="space-y-2.5 text-sm">
          <div class="flex justify-between">
            <dt class="text-gray-500 dark:text-neutral-400">Reference</dt>
            <dd>
              <a href="{{ route('requisitions.show', $pr->id) }}"
                 class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                {{ $pr->reference_no }}
              </a>
            </dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-gray-500 dark:text-neutral-400">Requester</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $pr->requester->name }}</dd>
          </div>
          <div class="flex justify-between">
            <dt class="text-gray-500 dark:text-neutral-400">Department</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $pr->department->name }}</dd>
          </div>
          @if($pr->costCentre)
          <div class="flex justify-between">
            <dt class="text-gray-500 dark:text-neutral-400">Cost Centre</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $pr->costCentre->name }}</dd>
          </div>
          @endif
          <div class="flex justify-between border-t border-gray-100 dark:border-neutral-700 pt-2.5">
            <dt class="text-gray-500 dark:text-neutral-400">PR Status</dt>
            <dd>
              @php
                $prStatusColors = [
                    'paid'      => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                    'delivered' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
                    'rejected'  => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                    'suspended' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
                ];
                $prBadge = $prStatusColors[$pr->status] ?? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
              @endphp
              <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $prBadge }}">
                {{ ucwords(str_replace('_', ' ', $pr->status)) }}
              </span>
            </dd>
          </div>
        </dl>
      </div>
      @endif

      {{-- Supplier Contact --}}
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-5">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Supplier Contact</h2>
        <dl class="space-y-2 text-sm">
          <div>
            <dt class="text-xs text-gray-400 dark:text-neutral-500 uppercase tracking-wider">Name</dt>
            <dd class="font-medium text-gray-900 dark:text-white">{{ $po->supplier->name }}</dd>
          </div>
          @if($po->supplier->contact_person)
          <div>
            <dt class="text-xs text-gray-400 dark:text-neutral-500 uppercase tracking-wider">Contact Person</dt>
            <dd class="text-gray-700 dark:text-neutral-300">{{ $po->supplier->contact_person }}</dd>
          </div>
          @endif
          @if($po->supplier->contact_email)
          <div>
            <dt class="text-xs text-gray-400 dark:text-neutral-500 uppercase tracking-wider">Email</dt>
            <dd>
              <a href="mailto:{{ $po->supplier->contact_email }}"
                 class="text-emerald-600 dark:text-emerald-400 hover:underline">
                {{ $po->supplier->contact_email }}
              </a>
            </dd>
          </div>
          @endif
          @if($po->supplier->contact_phone)
          <div>
            <dt class="text-xs text-gray-400 dark:text-neutral-500 uppercase tracking-wider">Phone</dt>
            <dd class="text-gray-700 dark:text-neutral-300">{{ $po->supplier->contact_phone }}</dd>
          </div>
          @endif
          @if($po->supplier->zppa_registration)
          <div>
            <dt class="text-xs text-gray-400 dark:text-neutral-500 uppercase tracking-wider">ZPPA Reg.</dt>
            <dd class="text-gray-700 dark:text-neutral-300">{{ $po->supplier->zppa_registration }}</dd>
          </div>
          @endif
        </dl>
      </div>

    </div>
  </div>

  {{-- ================================================================
       Goods Receipt Modal
       ================================================================ --}}
  @if($showReceiveForm)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.cancelReceiveForm()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="cancelReceiveForm"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">

      <div class="flex items-start justify-between mb-5">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Enter Goods Receipt</h3>
          <p class="text-sm text-gray-500 dark:text-neutral-400 mt-0.5">
            Record the quantities received in this delivery. Leave a field empty or zero if an item was not received.
          </p>
        </div>
        <button wire:click="cancelReceiveForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-200 ml-4 flex-shrink-0">
          <ph-x weight="bold" class="text-lg"></ph-x>
        </button>
      </div>

      @error('receiveQuantities')
      <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-sm text-red-700 dark:text-red-300">
        {{ $message }}
      </div>
      @enderror

      <div class="space-y-3 mb-6">
        @foreach($po->items as $item)
        @php
          $received    = (float) ($item->quantity_received ?? 0);
          $ordered     = (float) $item->quantity;
          $outstanding = max(0, $ordered - $received);
        @endphp
        <div class="rounded-xl border border-gray-200 dark:border-neutral-700 p-4 bg-gray-50 dark:bg-neutral-700/40">
          <div class="flex items-start justify-between gap-4 mb-3">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->description }}</p>
              <div class="flex items-center gap-4 mt-1 text-xs text-gray-500 dark:text-neutral-400">
                <span>Ordered: <strong class="text-gray-700 dark:text-neutral-200">{{ number_format($ordered, 2) }} {{ $item->unit_of_measure }}</strong></span>
                <span>Previously received: <strong class="{{ $received > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 dark:text-neutral-400' }}">{{ number_format($received, 2) }}</strong></span>
                <span>Outstanding: <strong class="{{ $outstanding > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">{{ number_format($outstanding, 2) }}</strong></span>
              </div>
            </div>
          </div>

          @if($outstanding <= 0)
          <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/50 rounded-lg text-sm text-emerald-700 dark:text-emerald-300">
            <ph-check-circle weight="fill"></ph-check-circle>
            Fully received
          </div>
          @else
          <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-gray-700 dark:text-neutral-300 whitespace-nowrap">
              Qty receiving now
            </label>
            <input type="number"
              wire:model="receiveQuantities.{{ $item->id }}"
              min="0"
              max="{{ $outstanding }}"
              step="0.01"
              placeholder="0.00"
              class="flex-1 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-teal-500 focus:border-transparent">
            <span class="text-sm text-gray-500 dark:text-neutral-400 whitespace-nowrap">/ {{ number_format($outstanding, 2) }} max</span>
          </div>
          @error("receiveQuantities.{$item->id}")
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror
          @endif
        </div>
        @endforeach
      </div>

      <div class="flex gap-3 justify-end border-t border-gray-200 dark:border-neutral-700 pt-4">
        <button wire:click="cancelReceiveForm"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="submitGoodsReceipt"
          class="px-5 py-2 text-sm font-semibold text-white bg-teal-500 hover:bg-teal-600 rounded-lg transition-colors"
          wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="submitGoodsReceipt">
            <ph-package-check weight="bold" class="inline mr-1"></ph-package-check>
            Confirm Receipt
          </span>
          <span wire:loading wire:target="submitGoodsReceipt">Saving…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

</div>
