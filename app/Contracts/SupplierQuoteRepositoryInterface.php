<?php

namespace App\Contracts;

use App\Models\Procurement\SupplierQuote;
use Illuminate\Database\Eloquent\Collection;

interface SupplierQuoteRepositoryInterface extends BaseRepositoryInterface
{
    public function findByRequisition(int $prId): Collection;

    /** Quotes where the supplier actually responded (amount is not null). */
    public function findResponded(int $prId): Collection;

    /** Quotes where the supplier was contacted but gave no response. */
    public function findNoResponse(int $prId): Collection;

    /** The lowest-amount responded quote for a PR. */
    public function findLowestQuote(int $prId): ?SupplierQuote;

    /** True if all responded quotes exceed the committee threshold. */
    public function allQuotesExceedThreshold(int $prId, float $threshold): bool;
}
