@php
    $pr   = $this->pr;
    $user = auth()->user();

    $statusColors = [
        'draft'                  => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        'pending_hod'            => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'pending_stores'         => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
        'issued_from_stores'     => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300',
        'pending_bursar'         => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
        'pending_vc_requisition' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        'pending_procurement'    => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
        'pending_audit'          => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
        'pending_vc_payment'     => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        'pending_payment'        => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        'paid'                   => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        'delivered'              => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
        'rejected'               => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        'suspended'              => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
    ];

    $statusLabel    = str_replace('_', ' ', Str::title($pr->status));
    $statusBadge    = $statusColors[$pr->status] ?? 'bg-gray-100 text-gray-700';
    $decisionColors = [
        'approved'                => 'text-emerald-600 dark:text-emerald-400',
        'committed'               => 'text-emerald-600 dark:text-emerald-400',
        'issued'                  => 'text-cyan-600 dark:text-cyan-400',
        'partially_issued'        => 'text-amber-600 dark:text-amber-400',
        'noted'                   => 'text-blue-600 dark:text-blue-400',
        'rejected'                => 'text-red-600 dark:text-red-400',
        'not_committed'           => 'text-red-600 dark:text-red-400',
        'suspended'               => 'text-rose-600 dark:text-rose-400',
        'returned_for_revision'   => 'text-orange-600 dark:text-orange-400',
        'resubmitted'             => 'text-blue-600 dark:text-blue-400',
    ];
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

  {{-- Page header --}}
  <div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div class="flex items-start gap-4">
      <a href="{{ route('requisitions.index') }}"
         class="mt-1 w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-500 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors flex-shrink-0">
        <ph-arrow-left weight="bold" class="text-base"></ph-arrow-left>
      </a>
      <div>
        <div class="flex items-center gap-3 flex-wrap">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pr->reference_no }}</h1>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusBadge }}">
            {{ $statusLabel }}
          </span>
          @if($pr->service_consumed_flag)
          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
            <ph-warning weight="fill"></ph-warning>
            Service Consumed Before PR
          </span>
          @endif
        </div>
        <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
          Submitted by {{ $pr->requester->name }} &middot; {{ $pr->created_at->format('d M Y, H:i') }}
        </p>
      </div>
    </div>
    @if(in_array($pr->status, ['draft', 'pending_hod']) && $user->can('pr.edit') && ($user->hasRole('admin') || $pr->requester_id === $user->id))
    <a href="{{ route('requisitions.edit', $pr->id) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 dark:border-neutral-700 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors flex-shrink-0 self-start">
      <ph-pencil-simple weight="bold" class="text-base"></ph-pencil-simple>
      Edit
    </a>
    @endif
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left column: main details --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- PR Details --}}
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Requisition Details</h2>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Type</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $pr->type }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Department</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $pr->department->name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Cost Centre</dt>
            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $pr->costCentre->name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Est. Total</dt>
            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
              ZMW {{ number_format($pr->totalEstimated(), 2) }}
            </dd>
          </div>
          @if($pr->notes)
          <div class="col-span-2">
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Notes</dt>
            <dd class="mt-1 text-sm text-gray-700 dark:text-neutral-300">{{ $pr->notes }}</dd>
          </div>
          @endif
        </dl>
      </div>

      {{-- Line Items --}}
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Line Items</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-neutral-700/50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Description</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Category</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Qty</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Unit</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Unit Price</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
              @foreach($pr->items as $item)
              <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition-colors">
                <td class="px-4 py-3 text-gray-900 dark:text-white">
                  {{ $item->description }}
                  @if($item->stockItem)
                  <span class="block text-xs text-gray-400 dark:text-neutral-500">{{ $item->stockItem->stock_code }}</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-gray-500 dark:text-neutral-400">{{ $item->category ?? '—' }}</td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ $item->quantity }}</td>
                <td class="px-4 py-3 text-gray-500 dark:text-neutral-400">{{ $item->unit_of_measure ?? '—' }}</td>
                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($item->unit_price_estimated, 2) }}</td>
                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ number_format($item->total_price_estimated, 2) }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-neutral-700/50">
              <tr>
                <td colspan="5" class="px-4 py-3 text-right text-sm font-semibold text-gray-700 dark:text-neutral-300">Estimated Total</td>
                <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                  ZMW {{ number_format($pr->totalEstimated(), 2) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- Supplier Quotes (if past procurement stage) --}}
      @if($pr->supplierQuotes->isNotEmpty())
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-900 dark:text-white">Supplier Quotes</h2>
          <span class="text-xs text-gray-500 dark:text-neutral-400">{{ $pr->supplierQuotes->count() }} quote(s)</span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-neutral-700/50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Supplier</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Amount</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Received</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Notes</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
              @foreach($pr->supplierQuotes as $quote)
              <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition-colors">
                <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $quote->supplier->name }}</td>
                <td class="px-4 py-3 text-right font-semibold {{ $quote->amount ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-neutral-500' }}">
                  {{ $quote->amount ? 'ZMW ' . number_format($quote->amount, 2) : 'No response' }}
                </td>
                <td class="px-4 py-3 text-gray-500 dark:text-neutral-400">
                  {{ $quote->received_date?->format('d M Y') ?? '—' }}
                </td>
                <td class="px-4 py-3 text-gray-500 dark:text-neutral-400">{{ $quote->notes ?? '—' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif

      {{-- Purchase Order (if exists) --}}
      @if($pr->purchaseOrder)
      @php $po = $pr->purchaseOrder; @endphp
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Purchase Order</h2>
        <dl class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">PO Number</dt>
            <dd class="mt-1">
              <a href="{{ route('po.show', $po->id) }}"
                 class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                {{ $po->po_number }}
              </a>
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Supplier</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $po->supplier->name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Total Value</dt>
            <dd class="mt-1 text-sm font-bold text-gray-900 dark:text-white">ZMW {{ number_format($po->total_value, 2) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400 uppercase tracking-wider">Status</dt>
            <dd class="mt-1 text-sm font-medium capitalize text-gray-900 dark:text-white">{{ str_replace('_', ' ', $po->status) }}</dd>
          </div>
        </dl>
      </div>
      @endif

      {{-- Attachments --}}
      @php
        $attachmentTypeLabels = [
          'memo'              => 'Memo',
          'quote_evidence'    => 'Quote Evidence',
          'committee_minutes' => 'Committee Minutes',
          'specification'     => 'Specification / TOR',
        ];
        $canUpload = ! in_array($pr->status, ['rejected', 'suspended', 'delivered'])
          && (
            $pr->requester_id === $user->id
            || $user->hasAnyRole(['procurement', 'auditor', 'admin'])
          );
        $attachmentsLocked = $pr->purchaseOrder
          && in_array($pr->purchaseOrder->status, ['issued', 'partially_delivered', 'delivered']);
      @endphp
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <ph-paperclip weight="fill" class="text-lg text-gray-500 dark:text-neutral-400"></ph-paperclip>
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Attachments</h2>
            @if($pr->attachments->isNotEmpty())
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 dark:bg-neutral-700 text-xs font-semibold text-gray-600 dark:text-neutral-300">
              {{ $pr->attachments->count() }}
            </span>
            @endif
            @if($attachmentsLocked)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
              <ph-lock weight="fill" class="text-xs"></ph-lock>
              Locked — PO issued
            </span>
            @endif
          </div>
          @if($canUpload)
          <button wire:click="openUploadModal"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-neutral-300 border border-gray-300 dark:border-neutral-600 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
            <ph-paperclip weight="bold" class="text-base"></ph-paperclip>
            Upload Attachment
          </button>
          @endif
        </div>

        @if($pr->attachments->isEmpty())
        <div class="px-6 py-8 text-center">
          <ph-file-dashed weight="regular" class="text-3xl text-gray-300 dark:text-neutral-600 mx-auto mb-2"></ph-file-dashed>
          <p class="text-sm text-gray-500 dark:text-neutral-400">No attachments yet.</p>
        </div>
        @else
        <ul class="divide-y divide-gray-100 dark:divide-neutral-700">
          @foreach($pr->attachments as $attachment)
          <li class="flex items-center gap-4 px-6 py-3 hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition-colors">
            {{-- File type icon --}}
            @php
              $iconName = match(true) {
                str_contains($attachment->mime_type, 'pdf')   => 'ph-file-pdf',
                str_contains($attachment->mime_type, 'image') => 'ph-file-image',
                default                                        => 'ph-file-doc',
              };
            @endphp
            <{{ $iconName }} weight="fill" class="text-2xl text-gray-400 dark:text-neutral-500 flex-shrink-0"></{{ $iconName }}>

            {{-- File details --}}
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $attachment->file_name }}</p>
              <p class="text-xs text-gray-500 dark:text-neutral-400">
                {{ $attachmentTypeLabels[$attachment->attachment_type] ?? $attachment->attachment_type }}
                &middot;
                {{ number_format($attachment->file_size / 1024, 0) }} KB
                &middot;
                {{ $attachment->uploader->name }}
                &middot;
                {{ $attachment->created_at->format('d M Y') }}
              </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
              <a href="{{ route('attachments.download', $attachment->id) }}"
                 target="_blank"
                 class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-neutral-400 border border-gray-300 dark:border-neutral-600 rounded-md hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors">
                <ph-download-simple weight="bold" class="text-sm"></ph-download-simple>
                Download
              </a>
              @if(! $attachmentsLocked && ($attachment->uploaded_by === $user->id || $user->hasRole('admin')))
              <button wire:click="deleteAttachment({{ $attachment->id }})"
                      wire:confirm="Delete this attachment? This cannot be undone."
                      class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-red-600 dark:text-red-400 border border-red-300 dark:border-red-700/50 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <ph-trash weight="bold" class="text-sm"></ph-trash>
                Delete
              </button>
              @endif
            </div>
          </li>
          @endforeach
        </ul>
        @endif
      </div>

    </div>

    {{-- Right column: approval trail + actions --}}
    <div class="space-y-6">

      {{-- Action Panel --}}
      @php
        $canAct =
          ($pr->status === 'pending_hod'            && $user->hasRole('hod'))            ||
          ($pr->status === 'pending_stores'          && $user->hasRole('stores'))         ||
          ($pr->status === 'pending_bursar'          && $user->hasRole('bursar'))         ||
          ($pr->status === 'pending_vc_requisition'  && $user->hasRole('vc'))             ||
          ($pr->status === 'pending_procurement'     && $user->hasRole('procurement'))    ||
          ($pr->status === 'pending_audit'           && $user->hasRole('auditor'))        ||
          ($pr->status === 'pending_vc_payment'      && $user->hasRole('vc'))             ||
          ($pr->status === 'pending_payment'         && $user->hasRole('bursar'));
      @endphp

      {{-- Draft: requester resubmits after revision --}}
      @if($pr->status === 'draft' && ($pr->requester_id === $user->id || $user->hasRole('admin')))
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-blue-200 dark:border-blue-800/60 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
          <ph-arrow-u-up-right weight="fill" class="inline text-blue-500 mr-1"></ph-arrow-u-up-right>
          Revision Required
        </h2>
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">
          This PR was returned for revision. Review the comments in the approval trail, make any necessary edits, then resubmit for approval.
        </p>
        <button wire:click="resubmit"
          wire:confirm="Resubmit this PR for approval? It will be sent back to the HOD for review."
          class="w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors">
          <ph-paper-plane-tilt weight="bold" class="inline mr-1"></ph-paper-plane-tilt>
          Resubmit for Approval
        </button>
      </div>
      @endif

      {{-- Fallback: PO missing at payment stage — procurement must act --}}
      @if(in_array($pr->status, ['pending_vc_payment', 'pending_payment']) && ! $pr->purchaseOrder)
      <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-300 dark:border-red-700/60 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
          <ph-warning weight="fill" class="inline text-red-500 mr-1"></ph-warning>
          Purchase Order Missing
        </h2>
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">
          No Purchase Order was raised during procurement. Payment cannot be processed without one.
        </p>
        @if($user->hasRole('procurement'))
        <a href="{{ route('po.create', $pr->id) }}"
           class="flex items-center justify-center gap-2 w-full py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors">
          <ph-plus weight="bold"></ph-plus>
          Create Purchase Order Now
        </a>
        @endif
      </div>
      @endif

      @if($canAct)
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
          <ph-lightning-a weight="fill" class="inline text-amber-500 mr-1"></ph-lightning-a>
          Your Action Required
        </h2>

        {{-- HOD actions --}}
        @if($pr->status === 'pending_hod')
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">Review this requisition and approve or reject it.</p>
        <div class="flex gap-3">
          <button wire:click="openAction('approved')"
            class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-check weight="bold" class="inline mr-1"></ph-check> Approve
          </button>
          <button wire:click="openAction('rejected')"
            class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-x weight="bold" class="inline mr-1"></ph-x> Reject
          </button>
        </div>
        @endif

        {{-- Stores actions --}}
        @if($pr->status === 'pending_stores')
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">Check if items are available in stores.</p>
        <div class="flex gap-3">
          <button wire:click="openStoresIssuance"
            class="flex-1 py-2.5 bg-cyan-500 hover:bg-cyan-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-package weight="bold" class="inline mr-1"></ph-package> Issue from Stores
          </button>
          <button wire:click="openAction('approved')"
            class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-arrow-right weight="bold" class="inline mr-1"></ph-arrow-right> Not in Stock
          </button>
        </div>
        @endif

        {{-- Bursar actions --}}
        @if($pr->status === 'pending_bursar')
        @php $unfulfilledAmt = $pr->unfulfilledValue(); $storesAmt = $pr->totalEstimated() - $unfulfilledAmt; @endphp
        <div class="mb-4">
          <p class="text-sm text-gray-600 dark:text-neutral-400">
            Confirm budget availability for <strong class="text-gray-900 dark:text-white">ZMW {{ number_format($unfulfilledAmt, 2) }}</strong>.
          </p>
          @if($storesAmt > 0)
          <p class="text-xs text-cyan-600 dark:text-cyan-400 mt-1">
            <ph-package weight="fill" class="inline"></ph-package>
            ZMW {{ number_format($storesAmt, 2) }} already covered by stores issuance.
          </p>
          @endif
        </div>
        <div class="flex gap-3">
          <button wire:click="openAction('committed')"
            class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-seal-check weight="bold" class="inline mr-1"></ph-seal-check> Commit Funds
          </button>
          <button wire:click="openAction('not_committed')"
            class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-x weight="bold" class="inline mr-1"></ph-x> Decline
          </button>
        </div>
        @endif

        {{-- VC Requisition actions --}}
        @if($pr->status === 'pending_vc_requisition')
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">Review and approve or reject the requisition.</p>
        <div class="flex gap-3">
          <button wire:click="openAction('approved')"
            class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-check weight="bold" class="inline mr-1"></ph-check> Approve
          </button>
          <button wire:click="openAction('rejected')"
            class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-x weight="bold" class="inline mr-1"></ph-x> Reject
          </button>
        </div>
        @endif

        {{-- Procurement actions --}}
        @if($pr->status === 'pending_procurement')
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-3">Add supplier quotes, raise a Purchase Order, then submit for audit.</p>
        <button wire:click="openQuoteForm"
          class="w-full mb-2 py-2.5 bg-violet-500 hover:bg-violet-600 text-white text-sm font-semibold rounded-lg transition-colors">
          <ph-plus weight="bold" class="inline mr-1"></ph-plus> Add Supplier Quote
        </button>
        @if(! $pr->purchaseOrder)
        <a href="{{ route('po.create', $pr->id) }}"
          class="flex items-center justify-center gap-2 w-full mb-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition-colors">
          <ph-file-plus weight="bold"></ph-file-plus> Create Purchase Order
        </a>
        @else
        <div class="flex items-center gap-2 w-full mb-2 px-4 py-2.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-300 dark:border-emerald-700/60 rounded-lg">
          <ph-check-circle weight="fill" class="text-emerald-500 flex-shrink-0"></ph-check-circle>
          <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
            PO raised: <a href="{{ route('po.show', $pr->purchaseOrder->id) }}" class="underline">{{ $pr->purchaseOrder->po_number }}</a>
          </span>
        </div>
        @endif
        <button wire:click="submitToAudit"
          class="w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors">
          <ph-arrow-right weight="bold" class="inline mr-1"></ph-arrow-right> Submit for Audit
        </button>
        @endif

        {{-- Audit actions --}}
        @if($pr->status === 'pending_audit')
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">Perform compliance review and record your finding.</p>
        <div class="flex gap-3">
          <button wire:click="openAction('noted')"
            class="flex-1 py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-seal-check weight="bold" class="inline mr-1"></ph-seal-check> Note (Pass)
          </button>
          <button wire:click="openAction('rejected')"
            class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-x weight="bold" class="inline mr-1"></ph-x> Reject
          </button>
        </div>
        @endif

        {{-- VC Payment actions --}}
        @if($pr->status === 'pending_vc_payment')

        {{-- Cost centre budget snapshot --}}
        @if($this->costCentreBudget)
        @php $budget = $this->costCentreBudget; $allocated = (float)$budget->allocated_amount; $committed = (float)$budget->committed_amount; $spent = (float)$budget->spent_amount; $available = $budget->availableBalance(); $usedPct = $allocated > 0 ? min(100, round(($committed + $spent) / $allocated * 100)) : 0; @endphp
        <div class="mb-4 rounded-xl border border-indigo-200 dark:border-indigo-700/60 bg-indigo-50 dark:bg-indigo-900/20 p-4">
          <div class="flex items-center gap-2 mb-3">
            <ph-piggy-bank weight="fill" class="text-base text-indigo-500"></ph-piggy-bank>
            <span class="text-xs font-semibold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">Cost Centre Budget — {{ now()->year }}</span>
          </div>
          <p class="text-xs font-medium text-indigo-800 dark:text-indigo-200 mb-2">{{ $budget->costCentre->name }}</p>
          <dl class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs mb-3">
            <div class="flex justify-between col-span-2 sm:col-span-1">
              <dt class="text-indigo-600 dark:text-indigo-400">Allocated</dt>
              <dd class="font-semibold text-gray-900 dark:text-white">ZMW {{ number_format($allocated, 2) }}</dd>
            </div>
            <div class="flex justify-between col-span-2 sm:col-span-1">
              <dt class="text-indigo-600 dark:text-indigo-400">Committed</dt>
              <dd class="font-semibold text-gray-900 dark:text-white">ZMW {{ number_format($committed, 2) }}</dd>
            </div>
            <div class="flex justify-between col-span-2 sm:col-span-1">
              <dt class="text-indigo-600 dark:text-indigo-400">Spent</dt>
              <dd class="font-semibold text-gray-900 dark:text-white">ZMW {{ number_format($spent, 2) }}</dd>
            </div>
            <div class="flex justify-between col-span-2 sm:col-span-1">
              <dt class="text-indigo-600 dark:text-indigo-400">Available</dt>
              <dd class="font-bold {{ $available < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">ZMW {{ number_format($available, 2) }}</dd>
            </div>
          </dl>
          <div class="w-full bg-indigo-200 dark:bg-indigo-800 rounded-full h-1.5">
            <div class="h-1.5 rounded-full {{ $usedPct >= 90 ? 'bg-red-500' : ($usedPct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                 style="width: {{ $usedPct }}%"></div>
          </div>
          <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">{{ $usedPct }}% of budget used (committed + spent)</p>
        </div>
        @endif

        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">Review and approve or suspend this payment.</p>
        <div class="flex gap-3">
          <button wire:click="openAction('approved')"
            class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-check weight="bold" class="inline mr-1"></ph-check> Approve Payment
          </button>
          <button wire:click="openAction('suspended')"
            class="flex-1 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-pause weight="bold" class="inline mr-1"></ph-pause> Suspend
          </button>
        </div>
        @endif

        {{-- Bursar payment processing --}}
        @if($pr->status === 'pending_payment')

        {{-- Cost centre budget snapshot --}}
        @if($this->costCentreBudget)
        @php $budget = $this->costCentreBudget; $allocated = (float)$budget->allocated_amount; $committed = (float)$budget->committed_amount; $spent = (float)$budget->spent_amount; $available = $budget->availableBalance(); $usedPct = $allocated > 0 ? min(100, round(($committed + $spent) / $allocated * 100)) : 0; @endphp
        <div class="mb-4 rounded-xl border border-emerald-200 dark:border-emerald-700/60 bg-emerald-50 dark:bg-emerald-900/20 p-4">
          <div class="flex items-center gap-2 mb-3">
            <ph-piggy-bank weight="fill" class="text-base text-emerald-600"></ph-piggy-bank>
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Cost Centre Budget — {{ now()->year }}</span>
          </div>
          <p class="text-xs font-medium text-emerald-800 dark:text-emerald-200 mb-2">{{ $budget->costCentre->name }}</p>
          <dl class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs mb-3">
            <div class="flex justify-between col-span-2 sm:col-span-1">
              <dt class="text-emerald-700 dark:text-emerald-400">Allocated</dt>
              <dd class="font-semibold text-gray-900 dark:text-white">ZMW {{ number_format($allocated, 2) }}</dd>
            </div>
            <div class="flex justify-between col-span-2 sm:col-span-1">
              <dt class="text-emerald-700 dark:text-emerald-400">Committed</dt>
              <dd class="font-semibold text-gray-900 dark:text-white">ZMW {{ number_format($committed, 2) }}</dd>
            </div>
            <div class="flex justify-between col-span-2 sm:col-span-1">
              <dt class="text-emerald-700 dark:text-emerald-400">Spent</dt>
              <dd class="font-semibold text-gray-900 dark:text-white">ZMW {{ number_format($spent, 2) }}</dd>
            </div>
            <div class="flex justify-between col-span-2 sm:col-span-1">
              <dt class="text-emerald-700 dark:text-emerald-400">Available</dt>
              <dd class="font-bold {{ $available < 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">ZMW {{ number_format($available, 2) }}</dd>
            </div>
          </dl>
          <div class="w-full bg-emerald-200 dark:bg-emerald-800 rounded-full h-1.5">
            <div class="h-1.5 rounded-full {{ $usedPct >= 90 ? 'bg-red-500' : ($usedPct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                 style="width: {{ $usedPct }}%"></div>
          </div>
          <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-1">{{ $usedPct }}% of budget used (committed + spent)</p>
        </div>
        @endif

        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-4">Confirm funds availability and process payment.</p>
        <div class="flex gap-3">
          <button wire:click="openAction('paid')"
            class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-money weight="bold" class="inline mr-1"></ph-money> Mark as Paid
          </button>
          <button wire:click="openAction('delay')"
            class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg transition-colors">
            <ph-clock weight="bold" class="inline mr-1"></ph-clock> Flag Delay
          </button>
        </div>
        @endif

      </div>
      {{-- Return for Revision — shown at the bottom of the action panel for any actor who can act --}}
      @if($canAct && $this->returnTargets)
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-orange-200 dark:border-orange-800/60 p-4">
        <button wire:click="openReturnModal"
          class="w-full flex items-center justify-center gap-2 py-2 text-sm font-semibold text-orange-600 dark:text-orange-400 border border-orange-300 dark:border-orange-700 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors">
          <ph-arrow-counter-clockwise weight="bold"></ph-arrow-counter-clockwise>
          Return for Revision
        </button>
        <p class="mt-2 text-xs text-center text-gray-400 dark:text-neutral-500">
          Send this PR back to an earlier stage with a mandatory explanation.
        </p>
      </div>
      @endif

      @endif

      {{-- Stores: confirm goods received after payment --}}
      @if(in_array($pr->status, ['paid']) && $user->hasRole('stores') && $pr->purchaseOrder)
      @php $deliveryPo = $pr->purchaseOrder; @endphp
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
          <ph-lightning-a weight="fill" class="inline text-amber-500 mr-1"></ph-lightning-a>
          Your Action Required
        </h2>
        <p class="text-sm text-gray-600 dark:text-neutral-400 mb-1">
          This requisition has been paid. Confirm that the goods have been received from the supplier.
        </p>
        @php
          $poStatusLabel = match($deliveryPo->status) {
              'issued'              => 'Awaiting first delivery',
              'partially_delivered' => 'Partially delivered — more items outstanding',
              default               => ucwords(str_replace('_', ' ', $deliveryPo->status)),
          };
          $poStatusColor = $deliveryPo->status === 'partially_delivered'
              ? 'text-blue-600 dark:text-blue-400'
              : 'text-amber-600 dark:text-amber-400';
        @endphp
        <p class="text-xs {{ $poStatusColor }} mb-4 font-medium">
          PO status: {{ $poStatusLabel }}
        </p>
        <a href="{{ route('po.show', $deliveryPo->id) }}"
           class="flex items-center justify-center gap-2 w-full py-2.5 bg-teal-500 hover:bg-teal-600 text-white text-sm font-semibold rounded-lg transition-colors">
          <ph-package-check weight="bold"></ph-package-check>
          Confirm Goods Received &rarr; {{ $deliveryPo->po_number }}
        </a>
      </div>
      @endif

      {{-- Approval Timeline --}}
      <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Approval Trail</h2>

        @if($pr->approvalRecords->isEmpty())
        <p class="text-sm text-gray-500 dark:text-neutral-400 text-center py-4">No approvals recorded yet.</p>
        @else
        <ol class="relative border-l border-gray-200 dark:border-neutral-600 ml-3 space-y-5">
          @foreach($pr->approvalRecords as $record)
          @php
            $dClass = $decisionColors[$record->decision] ?? 'text-gray-600 dark:text-neutral-400';
          @endphp
          <li class="ms-4">
            <div class="absolute w-3 h-3 rounded-full -start-1.5 border-2 border-white dark:border-neutral-800
              {{ in_array($record->decision, ['rejected','not_committed','suspended']) ? 'bg-red-400' : ($record->decision === 'returned_for_revision' ? 'bg-orange-400' : ($record->decision === 'resubmitted' ? 'bg-blue-400' : 'bg-emerald-400')) }}">
            </div>
            <div class="text-xs text-gray-400 dark:text-neutral-500 mb-0.5">
              {{ $record->created_at->format('d M Y, H:i') }}
            </div>
            <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200">
              <span class="{{ $dClass }}">{{ Str::ucfirst(str_replace('_', ' ', $record->decision)) }}</span>
              <span class="font-normal text-gray-500 dark:text-neutral-400">— {{ Str::title(str_replace('_', ' ', $record->stage)) }}</span>
            </p>
            <p class="text-xs text-gray-500 dark:text-neutral-400">by {{ $record->actor->name }}</p>
            @if($record->comment)
            <p class="mt-1 text-xs italic text-gray-500 dark:text-neutral-400 bg-gray-50 dark:bg-neutral-700/50 rounded px-2 py-1">
              "{{ $record->comment }}"
            </p>
            @endif
          </li>
          @endforeach
        </ol>
        @endif
      </div>

    </div>
  </div>

  {{-- ================================================================
       Action Confirmation Modal
       ================================================================ --}}
  @if($showActionModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.cancelAction()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="cancelAction"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-md p-6">

      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Confirm Action</h3>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mb-5">
        Decision: <strong class="text-gray-800 dark:text-neutral-200">{{ Str::ucfirst(str_replace('_', ' ', $pendingDecision)) }}</strong>
      </p>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
          Comment
          @if(in_array($pendingDecision, ['rejected','not_committed','suspended','delay']))
            <span class="text-red-500">*</span>
          @else
            <span class="text-gray-400 dark:text-neutral-500 font-normal">(optional)</span>
          @endif
        </label>
        <textarea wire:model="actionComment" rows="3"
          placeholder="Add a comment…"
          class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"></textarea>
        @error('actionComment')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex gap-3 justify-end">
        <button wire:click="cancelAction"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="confirmAction"
          class="px-5 py-2 text-sm font-semibold text-white rounded-lg transition-colors
            {{ in_array($pendingDecision, ['rejected','not_committed','suspended']) ? 'bg-red-500 hover:bg-red-600' : 'bg-emerald-500 hover:bg-emerald-600' }}"
          wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="confirmAction">Confirm</span>
          <span wire:loading wire:target="confirmAction">Processing…</span>
        </button>
      </div>
    </div>
  </div>
  @endif

  {{-- ================================================================
       Stores Issuance Modal
       ================================================================ --}}
  @if($showStoresIssuanceForm)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.cancelStoresIssuance()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="cancelStoresIssuance"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">

      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Issue from Stores</h3>
      <p class="text-sm text-gray-500 dark:text-neutral-400 mb-5">
        Enter the quantity you can issue for each item. Set to <strong>0</strong> for items not in stock — they will be forwarded to procurement automatically.
      </p>

      <div class="space-y-4 mb-5">
        @foreach($pr->items as $item)
        {{-- Alpine tracks qty locally for instant badge + dropdown reactivity --}}
        <div x-data="{ qty: {{ (float) ($storesQtyIssued[$item->id] ?? $item->quantity) }} }"
             class="rounded-lg border border-gray-200 dark:border-neutral-700 p-3 bg-gray-50 dark:bg-neutral-700/40">

          {{-- Item header --}}
          <div class="flex items-start justify-between gap-3 mb-3">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->description }}</p>
              <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
                Requested: {{ number_format($item->quantity, 2) }} {{ $item->unit_of_measure }}
              </p>
            </div>
            {{-- Live status badge --}}
            <div class="text-xs font-semibold flex-shrink-0 mt-0.5">
              <span x-show="qty >= {{ (float) $item->quantity }}"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300">
                <ph-check-circle weight="fill"></ph-check-circle> From stores
              </span>
              <span x-show="qty > 0 && qty < {{ (float) $item->quantity }}" x-cloak
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                <ph-split-vertical weight="bold"></ph-split-vertical> Partial
              </span>
              <span x-show="qty <= 0" x-cloak
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-400">
                <ph-arrow-right weight="bold"></ph-arrow-right> To procurement
              </span>
            </div>
          </div>

          {{-- Qty to issue input --}}
          <div class="mb-2">
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Qty to Issue</label>
            <input type="number"
              x-model.number="qty"
              wire:model.lazy="storesQtyIssued.{{ $item->id }}"
              min="0"
              max="{{ (float) $item->quantity }}"
              step="0.01"
              class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
            @error('storesQtyIssued.' . $item->id)
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          {{-- Stock item dropdown — disabled when qty = 0 --}}
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">
              Stock Item <span x-show="qty > 0" class="text-red-500">*</span>
              <span x-show="qty <= 0" x-cloak class="font-normal text-gray-400">(not required)</span>
            </label>
            <select wire:model="storesLinks.{{ $item->id }}"
              :disabled="qty <= 0"
              :class="qty <= 0 ? 'opacity-50 cursor-not-allowed' : ''"
              class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-cyan-500 focus:border-transparent transition-opacity">
              <option value="">— Select stock item —</option>
              @foreach($this->stockedItems as $stockItem)
              <option value="{{ $stockItem->id }}">
                [{{ $stockItem->stock_code }}] {{ $stockItem->description }}
                ({{ number_format($stockItem->quantity_on_hand, 2) }} {{ $stockItem->unit_of_measure }} available)
              </option>
              @endforeach
            </select>
            @error('storesLinks.' . $item->id)
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

        </div>
        @endforeach
      </div>

      <div class="mb-5">
        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
          Comment <span class="text-gray-400 dark:text-neutral-500 font-normal">(optional)</span>
        </label>
        <textarea wire:model="storesComment" rows="2"
          placeholder="Any notes about this issuance…"
          class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-cyan-500 focus:border-transparent resize-none"></textarea>
      </div>

      <div class="flex gap-3 justify-end">
        <button wire:click="cancelStoresIssuance"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="confirmStoresIssuance"
          class="px-5 py-2 text-sm font-semibold text-white bg-cyan-500 hover:bg-cyan-600 rounded-lg transition-colors"
          wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="confirmStoresIssuance">Confirm Issuance</span>
          <span wire:loading wire:target="confirmStoresIssuance">Processing…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

  {{-- ================================================================
       Upload Attachment Modal
       ================================================================ --}}
  @if($showUploadModal)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.cancelUpload()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="cancelUpload"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-md p-6">

      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5">Upload Attachment</h3>

      <div class="space-y-4">

        {{-- Document type --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Document Type <span class="text-red-500">*</span>
          </label>
          <select wire:model="uploadType"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Select type…</option>
            <option value="memo">Memo</option>
            <option value="quote_evidence">Quote Evidence</option>
            <option value="committee_minutes">Committee Minutes</option>
            <option value="specification">Specification / Terms of Reference</option>
          </select>
          @error('uploadType') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- File picker --}}
        <div x-data="{ fileName: '' }">
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            File <span class="text-red-500">*</span>
          </label>
          <label
            class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-xl cursor-pointer transition-colors"
            :class="fileName ? 'border-blue-400 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-neutral-600 bg-gray-50 dark:bg-neutral-700/40 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/10'">
            <div class="flex flex-col items-center justify-center gap-1 px-4 text-center" x-show="!fileName">
              <ph-upload-simple weight="regular" class="text-3xl text-gray-400 dark:text-neutral-500"></ph-upload-simple>
              <p class="text-sm text-gray-600 dark:text-neutral-300 font-medium">Click to browse or drag & drop</p>
              <p class="text-xs text-gray-400 dark:text-neutral-500">PDF, Word, JPEG, PNG — max 5 MB</p>
            </div>
            <div class="flex items-center gap-2 px-4" x-show="fileName" x-cloak>
              <ph-file-text weight="fill" class="text-2xl text-blue-500 flex-shrink-0"></ph-file-text>
              <span class="text-sm font-medium text-gray-800 dark:text-white truncate" x-text="fileName"></span>
            </div>
            <input type="file"
              wire:model="uploadFile"
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
              class="hidden"
              x-on:change="fileName = $event.target.files[0]?.name ?? ''">
          </label>

          <div wire:loading wire:target="uploadFile" class="flex items-center gap-1.5 mt-1.5">
            <div class="w-3 h-3 rounded-full border-2 border-blue-500 border-t-transparent animate-spin"></div>
            <p class="text-xs text-blue-500">Uploading file…</p>
          </div>

          @error('uploadFile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

      </div>

      <div class="flex gap-3 justify-end mt-6">
        <button wire:click="cancelUpload"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="submitUpload"
          wire:loading.attr="disabled"
          wire:loading.class="opacity-60 cursor-not-allowed"
          class="px-5 py-2 text-sm font-semibold text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors">
          <span wire:loading.remove wire:target="submitUpload">Upload</span>
          <span wire:loading wire:target="submitUpload">Uploading…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

  {{-- ================================================================
       Return for Revision Modal
       ================================================================ --}}
  @if($showReturnModal)
  @php
    $returnTargetLabels = [
      'draft'               => 'Requester — back to draft for correction',
      'pending_bursar'      => 'Bursar — re-evaluate budget commitment',
      'pending_procurement' => 'Procurement — fix quotes or Purchase Order',
      'pending_audit'       => 'Auditor — re-run compliance review',
      'pending_vc_payment'  => 'VC — re-approve payment',
    ];
  @endphp
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.cancelReturnModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="cancelReturnModal"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-md p-6">

      <div class="flex items-start gap-3 mb-5">
        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30 flex-shrink-0">
          <ph-arrow-counter-clockwise weight="bold" class="text-lg text-orange-500"></ph-arrow-counter-clockwise>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Return for Revision</h3>
          <p class="text-sm text-gray-500 dark:text-neutral-400 mt-0.5">
            The PR will be sent back to the selected stage. Approvals before that point remain valid.
            A detailed comment is required.
          </p>
        </div>
      </div>

      <div class="space-y-4 mb-6">

        {{-- Target stage --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Return to <span class="text-red-500">*</span>
          </label>
          <select wire:model="returnTargetStatus"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            <option value="">— Select stage —</option>
            @foreach($this->returnTargets as $status)
            <option value="{{ $status }}">
              {{ $returnTargetLabels[$status] ?? ucwords(str_replace('_', ' ', $status)) }}
            </option>
            @endforeach
          </select>
          @error('returnTargetStatus')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Comment --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Explanation <span class="text-red-500">*</span>
          </label>
          <textarea wire:model="returnComment" rows="4"
            placeholder="Clearly explain what needs to be corrected or addressed before this PR can proceed…"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"></textarea>
          @error('returnComment')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror
          <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">
            This comment will be visible to all parties in the approval trail.
          </p>
        </div>

      </div>

      <div class="flex gap-3 justify-end">
        <button wire:click="cancelReturnModal"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="confirmReturn"
          wire:loading.attr="disabled"
          class="px-5 py-2 text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors">
          <span wire:loading.remove wire:target="confirmReturn">
            <ph-arrow-counter-clockwise weight="bold" class="inline mr-1"></ph-arrow-counter-clockwise>
            Confirm Return
          </span>
          <span wire:loading wire:target="confirmReturn">Processing…</span>
        </button>
      </div>

    </div>
  </div>
  @endif

  {{-- ================================================================
       Add Quote Modal (Procurement)
       ================================================================ --}}
  @if($showQuoteForm)
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-data x-on:keydown.escape.window="$wire.cancelQuote()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="cancelQuote"></div>
    <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 w-full max-w-md p-6">

      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5">Record Supplier Quote</h3>

      <div class="space-y-4">

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Supplier <span class="text-red-500">*</span>
          </label>
          <select wire:model="quoteSupplier"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <option value="0">Select supplier…</option>
            @foreach($this->suppliers as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
          </select>
          @error('quoteSupplier') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
            Amount (ZMW) <span class="text-gray-400 font-normal">— leave blank if no response</span>
          </label>
          <input type="number" wire:model="quoteAmount" min="0" step="0.01"
            placeholder="0.00"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
          @error('quoteAmount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Received Date</label>
          <input type="date" wire:model="quoteDate"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Notes</label>
          <textarea wire:model="quoteNotes" rows="2"
            class="w-full rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"></textarea>
        </div>

      </div>

      <div class="flex gap-3 justify-end mt-5">
        <button wire:click="cancelQuote"
          class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700 rounded-lg transition-colors">
          Cancel
        </button>
        <button wire:click="saveQuote"
          class="px-5 py-2 text-sm font-semibold text-white bg-violet-500 hover:bg-violet-600 rounded-lg transition-colors"
          wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="saveQuote">Save Quote</span>
          <span wire:loading wire:target="saveQuote">Saving…</span>
        </button>
      </div>
    </div>
  </div>
  @endif

</div>
