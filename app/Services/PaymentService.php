<?php

namespace App\Services;

use App\Contracts\PaymentRepositoryInterface;
use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Models\Finance\Payment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepositoryInterface        $paymentRepo,
        private readonly PurchaseOrderRepositoryInterface  $poRepo,
        private readonly BudgetService                     $budgetService,
        private readonly AuditComplianceService            $auditService,
    ) {}

    /**
     * Initialise a Payment record when the PR reaches 'pending_payment'.
     * The payment amount defaults to the PO total value.
     */
    public function initiate(PurchaseOrder $po): Payment
    {
        if ($this->paymentRepo->findByPurchaseOrder($po->id)) {
            throw new RuntimeException("A payment record already exists for PO #{$po->po_number}.");
        }

        $payment = $this->paymentRepo->create([
            'purchase_order_id' => $po->id,
            'amount'            => $po->total_value,
            'status'            => 'pending',
        ]);

        $this->auditService->log(Payment::class, $payment->id, 'initiated', [], ['po_number' => $po->po_number]);

        return $payment;
    }

    /**
     * VC approves the payment.
     */
    public function recordVcApproval(Payment $payment, User $vc): Payment
    {
        if (! $vc->hasRole('vc')) {
            throw new RuntimeException('Only the VC can approve payments.');
        }

        if ($payment->vc_approved_at) {
            throw new RuntimeException('This payment has already been VC-approved.');
        }

        $this->paymentRepo->update($payment->id, [
            'vc_approved_by' => $vc->id,
            'vc_approved_at' => now(),
        ]);

        $this->auditService->log(Payment::class, $payment->id, 'vc_approved', [], ['approved_by' => $vc->name]);

        return $payment->fresh();
    }

    /**
     * Bursar confirms fund availability and either releases payment or flags a delay.
     */
    public function processBursarConfirmation(
        Payment $payment,
        User $bursar,
        bool $fundsAvailable,
        ?string $delayComment = null
    ): Payment {
        if (! $bursar->hasRole('bursar')) {
            throw new RuntimeException('Only the Bursar can confirm fund availability.');
        }

        if (! $payment->vc_approved_at) {
            throw new RuntimeException('Payment has not yet been approved by the VC.');
        }

        if (! $fundsAvailable) {
            if (blank($delayComment)) {
                throw new RuntimeException('A delay comment is required when funds are unavailable.');
            }

            $this->paymentRepo->update($payment->id, [
                'delay_comment'      => $delayComment,
                'bursar_confirmed_by'=> $bursar->id,
                'bursar_confirmed_at'=> now(),
            ]);

            $this->auditService->log(Payment::class, $payment->id, 'bursar_funds_unavailable', [], ['comment' => $delayComment]);

            return $payment->fresh();
        }

        return DB::transaction(function () use ($payment, $bursar) {
            $this->paymentRepo->update($payment->id, [
                'status'             => 'paid',
                'bursar_confirmed_by'=> $bursar->id,
                'bursar_confirmed_at'=> now(),
                'paid_at'            => now(),
            ]);

            // Move committed budget to spent.
            // Release the full estimated commitment; record only the actual payment as spent.
            // Any saving vs. the estimate is returned to the available balance.
            $po = $payment->purchaseOrder;
            $pr = $po->purchaseRequisition;
            $this->budgetService->recordSpend(
                $pr->cost_centre_id,
                (float) $payment->amount,
                (float) $pr->totalEstimated(),
            );

            // Advance the PR status
            $pr->update(['status' => 'paid']);

            // If procurement left the PO in draft, auto-issue it so stores can confirm goods received
            if ($po->status === 'draft') {
                $this->poRepo->update($po->id, [
                    'status'      => 'issued',
                    'issued_date' => now(),
                ]);
                $this->auditService->log(PurchaseOrder::class, $po->id, 'auto_issued_on_payment', ['status' => 'draft'], ['status' => 'issued']);
            }

            $this->auditService->log(Payment::class, $payment->id, 'paid', ['status' => 'pending'], ['status' => 'paid', 'paid_by' => $bursar->name]);

            return $payment->fresh();
        });
    }

    /**
     * Add or update a delay explanation on a pending payment.
     */
    public function recordDelayComment(Payment $payment, string $comment, User $actor): Payment
    {
        $this->paymentRepo->update($payment->id, ['delay_comment' => $comment]);

        $this->auditService->log(Payment::class, $payment->id, 'delay_noted', [], ['comment' => $comment, 'by' => $actor->name]);

        return $payment->fresh();
    }
}
