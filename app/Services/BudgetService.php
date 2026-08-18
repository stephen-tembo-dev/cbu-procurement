<?php

namespace App\Services;

use App\Contracts\BudgetAllocationRepositoryInterface;
use App\Models\Finance\BudgetAllocation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BudgetService
{
    public function __construct(
        private readonly BudgetAllocationRepositoryInterface $budgetRepo,
    ) {}

    /**
     * Return the current-year budget allocation for a cost centre.
     * Throws if no allocation has been set up for this year.
     */
    public function getCurrentAllocation(int $costCentreId): BudgetAllocation
    {
        $allocation = $this->budgetRepo->findCurrentYear($costCentreId);

        if (! $allocation) {
            throw new RuntimeException(
                "No budget allocation found for cost centre #{$costCentreId} in fiscal year " . now()->year
            );
        }

        return $allocation;
    }

    /**
     * Check whether a cost centre has sufficient uncommitted funds.
     */
    public function hasSufficientFunds(int $costCentreId, float $amount): bool
    {
        try {
            $allocation = $this->getCurrentAllocation($costCentreId);
        } catch (RuntimeException) {
            return false;
        }

        return $allocation->availableBalance() >= $amount;
    }

    /**
     * Commit an amount against a cost centre budget.
     * Called when the Bursar confirms a PR.
     */
    public function commitFunds(int $costCentreId, float $amount): void
    {
        $allocation = $this->getCurrentAllocation($costCentreId);

        if ($allocation->availableBalance() < $amount) {
            throw new RuntimeException(
                "Insufficient budget balance. Available: {$allocation->availableBalance()}, Requested: {$amount}"
            );
        }

        $this->budgetRepo->incrementCommitted($allocation->id, $amount);
    }

    /**
     * Release a previously committed amount (e.g. PR rejected after commitment).
     */
    public function releaseCommitment(int $costCentreId, float $amount): void
    {
        $allocation = $this->getCurrentAllocation($costCentreId);
        $this->budgetRepo->decrementCommitted($allocation->id, $amount);
    }

    /**
     * Move committed funds to spent when a payment is made.
     *
     * $committedAmount is the amount originally reserved (estimated PR total).
     * $actualAmount    is the actual payment (PO total value).
     * Any difference between the two is released back to available budget.
     */
    public function recordSpend(int $costCentreId, float $actualAmount, float $committedAmount): void
    {
        DB::transaction(function () use ($costCentreId, $actualAmount, $committedAmount) {
            $allocation = $this->getCurrentAllocation($costCentreId);
            // Release the full original reservation (not just the actual payment amount).
            $this->budgetRepo->decrementCommitted($allocation->id, $committedAmount);
            $this->budgetRepo->incrementSpent($allocation->id, $actualAmount);
        });
    }

    /**
     * Create or update a budget allocation for a cost centre and fiscal year.
     * Used by the Bursar during the annual budgeting process.
     */
    public function setAllocation(int $costCentreId, int $fiscalYear, float $amount): BudgetAllocation
    {
        return BudgetAllocation::updateOrCreate(
            ['cost_centre_id' => $costCentreId, 'fiscal_year' => $fiscalYear],
            ['allocated_amount' => $amount]
        );
    }
}
