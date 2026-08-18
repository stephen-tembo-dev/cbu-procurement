# Procurement System — Build Instructions
## Phase 2: Contracts, Repositories & Services

> Execute each step in order. Create each file exactly at the path shown.
> All paths are relative to the Laravel project root.

---

## OVERVIEW

```
app/
├── Contracts/
│   ├── BaseRepositoryInterface.php
│   ├── UserRepositoryInterface.php
│   ├── DepartmentRepositoryInterface.php
│   ├── CostCentreRepositoryInterface.php
│   ├── StockItemRepositoryInterface.php
│   ├── SupplierRepositoryInterface.php
│   ├── PurchaseRequisitionRepositoryInterface.php
│   ├── PurchaseRequisitionItemRepositoryInterface.php
│   ├── ApprovalRecordRepositoryInterface.php
│   ├── SupplierQuoteRepositoryInterface.php
│   ├── PurchaseOrderRepositoryInterface.php
│   ├── PaymentRepositoryInterface.php
│   ├── AttachmentRepositoryInterface.php
│   ├── BudgetAllocationRepositoryInterface.php
│   └── AuditLogRepositoryInterface.php
├── Repositories/
│   ├── BaseRepository.php
│   ├── UserRepository.php
│   ├── DepartmentRepository.php
│   ├── CostCentreRepository.php
│   ├── StockItemRepository.php
│   ├── SupplierRepository.php
│   ├── PurchaseRequisitionRepository.php
│   ├── PurchaseRequisitionItemRepository.php
│   ├── ApprovalRecordRepository.php
│   ├── SupplierQuoteRepository.php
│   ├── PurchaseOrderRepository.php
│   ├── PaymentRepository.php
│   ├── AttachmentRepository.php
│   ├── BudgetAllocationRepository.php
│   └── AuditLogRepository.php
├── Services/
│   ├── PurchaseRequisitionService.php
│   ├── WorkflowService.php
│   ├── StoresService.php
│   ├── ProcurementService.php
│   ├── PaymentService.php
│   ├── BudgetService.php
│   ├── AuditComplianceService.php
│   ├── ReportService.php
│   └── AttachmentService.php
└── Providers/
    └── RepositoryServiceProvider.php
```

---

## PART A — CONTRACTS (Interfaces)

---

### `app/Contracts/BaseRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Find a single record by primary key.
     * Returns null if not found (never throws).
     */
    public function findById(int $id, array $relations = []): ?Model;

    /**
     * Find a single record by a given column value.
     */
    public function findBy(string $column, mixed $value, array $relations = []): ?Model;

    /**
     * Find all records matching a key→value criteria array.
     */
    public function findWhere(array $criteria, array $relations = [], ?string $orderBy = null, string $direction = 'asc'): Collection;

    /**
     * Return a paginated or full collection.
     * Pass $perPage = null for a full Collection; an integer for paginated results.
     */
    public function all(array $relations = [], ?int $perPage = null, ?string $orderBy = null, string $direction = 'desc'): Collection|LengthAwarePaginator;

    /**
     * Persist a new record and return the hydrated model.
     */
    public function create(array $data): Model;

    /**
     * Update a record by primary key. Returns the updated model.
     * Throws ModelNotFoundException if the record does not exist.
     */
    public function update(int $id, array $data): Model;

    /**
     * Soft-delete (or hard-delete if model has no SoftDeletes) by primary key.
     */
    public function delete(int $id): bool;

    /**
     * Check whether a record exists without loading it.
     */
    public function exists(int $id): bool;

    /**
     * Return count of records matching optional criteria.
     */
    public function count(array $criteria = []): int;
}
```

---

### `app/Contracts/UserRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    /** All active users belonging to a department. */
    public function findByDepartment(int $departmentId, bool $activeOnly = true): Collection;

    /** All users carrying a given Spatie role name. */
    public function findByRole(string $roleName): Collection;

    /**
     * Paginated user list with optional search on name/email
     * and optional role filter.
     */
    public function paginate(
        int $perPage = 20,
        ?string $search = null,
        ?string $role = null
    ): LengthAwarePaginator;

    /** Toggle the is_active flag. */
    public function setActive(int $id, bool $active): bool;
}
```

---

### `app/Contracts/DepartmentRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?Department;

    public function findActive(): Collection;

    /** Return department with its cost centres and HOD eager-loaded. */
    public function findWithRelations(int $id): ?Department;

    /** Assign (or re-assign) the HOD for a department. */
    public function assignHod(int $departmentId, int $userId): bool;
}
```

---

### `app/Contracts/CostCentreRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\CostCentre;
use Illuminate\Database\Eloquent\Collection;

interface CostCentreRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?CostCentre;

    public function findByDepartment(int $departmentId, bool $activeOnly = true): Collection;

    /** Return cost centres with their current-year budget allocation. */
    public function findWithCurrentBudget(int $departmentId): Collection;
}
```

---

### `app/Contracts/StockItemRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\StockItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StockItemRepositoryInterface extends BaseRepositoryInterface
{
    public function findByStockCode(string $code): ?StockItem;

    /** Items that ARE stocked — users cannot order these directly. */
    public function findStocked(): Collection;

    /** Items users CAN directly request (is_stocked = false). */
    public function findOrderable(): Collection;

    /** Items whose quantity_on_hand is at or below reorder_level. */
    public function findBelowReorderLevel(): Collection;

    /** Adjust stock quantity. Pass positive delta to add, negative to deduct. */
    public function adjustQuantity(int $id, float $delta): bool;

    public function search(string $term, ?int $perPage = 20): LengthAwarePaginator;
}
```

---

### `app/Contracts/SupplierRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function findActive(): Collection;

    public function findZppaApproved(): Collection;

    public function search(string $term, int $perPage = 20): LengthAwarePaginator;

    /** How many quotes has this supplier submitted across all PRs? */
    public function getQuoteCount(int $supplierId): int;
}
```

---

### `app/Contracts/PurchaseRequisitionRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\PurchaseRequisition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseRequisitionRepositoryInterface extends BaseRepositoryInterface
{
    public function findByReference(string $reference): ?PurchaseRequisition;

    /**
     * Full PR with all relations needed to render the audit trail summary:
     * requester, department, costCentre, items.stockItem,
     * approvalRecords.actor, supplierQuotes.supplier,
     * purchaseOrder.supplier, purchaseOrder.payment,
     * attachments.uploader.
     */
    public function findWithFullTrail(int $id): ?PurchaseRequisition;

    /** PRs raised by a specific user. */
    public function findByRequester(int $userId, int $perPage = 20): LengthAwarePaginator;

    /** PRs belonging to a department. */
    public function findByDepartment(int $departmentId, int $perPage = 20): LengthAwarePaginator;

    /**
     * PRs sitting at a specific workflow stage, ready for action.
     * $status can be a single string or an array of statuses.
     */
    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator;

    /**
     * Dashboard inbox: PRs the given user needs to act on
     * based on their role and the current PR status.
     */
    public function findPendingForUser(int $userId, string $role): LengthAwarePaginator;

    /** Atomically update the status column. */
    public function updateStatus(int $id, string $status): bool;

    /**
     * Count PRs grouped by status.
     * Returns [ 'pending_hod' => 3, 'pending_stores' => 1, ... ]
     */
    public function countByStatus(): array;

    /**
     * PRs with a service_consumed_flag that have not yet been reviewed.
     */
    public function findFlaggedServiceConsumed(): Collection;

    /**
     * Advanced filter search used by reports and admin views.
     */
    public function search(
        ?string $reference = null,
        ?int $departmentId = null,
        ?int $requesterId = null,
        ?string $status = null,
        ?string $type = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator;
}
```

---

### `app/Contracts/PurchaseRequisitionItemRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\PurchaseRequisitionItem;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseRequisitionItemRepositoryInterface extends BaseRepositoryInterface
{
    public function findByRequisition(int $prId): Collection;

    /** Replace all items on a PR with a new set. */
    public function syncItems(int $prId, array $items): Collection;

    /** Recalculate and persist total_price_estimated for an item. */
    public function recalculateTotal(int $itemId): PurchaseRequisitionItem;
}
```

---

### `app/Contracts/ApprovalRecordRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\ApprovalRecord;
use Illuminate\Database\Eloquent\Collection;

interface ApprovalRecordRepositoryInterface extends BaseRepositoryInterface
{
    /** Full approval trail for a PR, ordered chronologically. */
    public function findByRequisition(int $prId): Collection;

    /** Most recent record for a PR at a given stage. */
    public function findLatestForStage(int $prId, string $stage): ?ApprovalRecord;

    /** Whether the given stage has been completed (any decision recorded). */
    public function stageCompleted(int $prId, string $stage): bool;

    /** The actor IDs that have approved at each stage, keyed by stage. */
    public function getApproverMap(int $prId): array;
}
```

---

### `app/Contracts/SupplierQuoteRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\SupplierQuote;
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
```

---

### `app/Contracts/PurchaseOrderRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PurchaseOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByPoNumber(string $poNumber): ?PurchaseOrder;

    public function findByRequisition(int $prId): ?PurchaseOrder;

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator;

    /** POs that have been issued but not yet confirmed as delivered. */
    public function findUndelivered(): Collection;

    /** POs for the reports screen with date range filtering. */
    public function findForReport(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator;

    public function updateStatus(int $id, string $status): bool;
}
```

---

### `app/Contracts/PaymentRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByPurchaseOrder(int $poId): ?Payment;

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator;

    /** Payments awaiting VC approval. */
    public function findPendingVcApproval(): Collection;

    /** Payments VC-approved but awaiting Bursar fund confirmation. */
    public function findPendingBursarConfirmation(): Collection;

    /** Payments with a delay comment (overdue). */
    public function findDelayed(): Collection;

    public function findForReport(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator;
}
```

---

### `app/Contracts/AttachmentRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Collection;

interface AttachmentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByRequisition(int $prId): Collection;

    /** Filter by attachment_type (e.g. 'memo', 'quote_evidence', 'committee_minutes'). */
    public function findByType(int $prId, string $type): Collection;

    /** Whether a PR has at least one attachment of the given type. */
    public function hasType(int $prId, string $type): bool;
}
```

---

### `app/Contracts/BudgetAllocationRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\BudgetAllocation;
use Illuminate\Database\Eloquent\Collection;

interface BudgetAllocationRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCostCentreAndYear(int $costCentreId, int $fiscalYear): ?BudgetAllocation;

    public function findCurrentYear(int $costCentreId): ?BudgetAllocation;

    /** All allocations for a given fiscal year, with cost centre and department. */
    public function findAllForYear(int $fiscalYear): Collection;

    /**
     * Atomically increment committed_amount.
     * Use DB lock to prevent race conditions on concurrent approvals.
     */
    public function incrementCommitted(int $id, float $amount): bool;

    public function decrementCommitted(int $id, float $amount): bool;

    public function incrementSpent(int $id, float $amount): bool;
}
```

---

### `app/Contracts/AuditLogRepositoryInterface.php`

```php
<?php

namespace App\Contracts;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AuditLogRepositoryInterface extends BaseRepositoryInterface
{
    /** Full log for a single entity (e.g. all events on PR #42). */
    public function findForEntity(string $entityType, int $entityId): Collection;

    /** Paginated log for an actor. */
    public function findByActor(int $actorId, int $perPage = 30): LengthAwarePaginator;

    /** Write an immutable log entry. This is the only write path for audit logs. */
    public function log(
        string $entityType,
        int $entityId,
        string $action,
        array $oldValues = [],
        array $newValues = [],
        ?int $actorId = null,
        ?string $ip = null
    ): AuditLog;
}
```

---

## PART B — REPOSITORIES

---

### `app/Repositories/BaseRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = app($this->model());
    }

    /**
     * Return the fully-qualified model class name.
     * Concrete repositories must implement this.
     */
    abstract protected function model(): string;

    // ── BaseRepositoryInterface implementation ─────────────────────────────

    public function findById(int $id, array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id);
    }

    public function findBy(string $column, mixed $value, array $relations = []): ?Model
    {
        return $this->model->with($relations)->where($column, $value)->first();
    }

    public function findWhere(
        array $criteria,
        array $relations = [],
        ?string $orderBy = null,
        string $direction = 'asc'
    ): Collection {
        $query = $this->model->with($relations)->where($criteria);

        if ($orderBy) {
            $query->orderBy($orderBy, $direction);
        }

        return $query->get();
    }

    public function all(
        array $relations = [],
        ?int $perPage = null,
        ?string $orderBy = null,
        string $direction = 'desc'
    ): Collection|LengthAwarePaginator {
        $query = $this->model->with($relations);

        if ($orderBy) {
            $query->orderBy($orderBy, $direction);
        }

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        $record = $this->model->findOrFail($id);

        return (bool) $record->delete();
    }

    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    public function count(array $criteria = []): int
    {
        return empty($criteria)
            ? $this->model->count()
            : $this->model->where($criteria)->count();
    }

    // ── Convenience helpers available to all concrete repositories ─────────

    protected function newQuery()
    {
        return $this->model->newQuery();
    }
}
```

---

### `app/Repositories/UserRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function model(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByDepartment(int $departmentId, bool $activeOnly = true): Collection
    {
        return $this->model
            ->where('department_id', $departmentId)
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    public function findByRole(string $roleName): Collection
    {
        return $this->model
            ->role($roleName)           // Spatie scope
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function paginate(
        int $perPage = 20,
        ?string $search = null,
        ?string $role = null
    ): LengthAwarePaginator {
        $query = $this->model
            ->with(['department', 'roles'])
            ->when($search, fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role, fn ($q) => $q->role($role))
            ->orderBy('name');

        return $query->paginate($perPage);
    }

    public function setActive(int $id, bool $active): bool
    {
        return (bool) $this->model->where('id', $id)->update(['is_active' => $active]);
    }
}
```

---

### `app/Repositories/DepartmentRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\DepartmentRepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    protected function model(): string
    {
        return Department::class;
    }

    public function findByCode(string $code): ?Department
    {
        return $this->model->where('code', $code)->first();
    }

    public function findActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findWithRelations(int $id): ?Department
    {
        return $this->model
            ->with(['hod', 'costCentres', 'users' => fn ($q) => $q->where('is_active', true)])
            ->find($id);
    }

    public function assignHod(int $departmentId, int $userId): bool
    {
        return (bool) $this->model
            ->where('id', $departmentId)
            ->update(['hod_user_id' => $userId]);
    }
}
```

---

### `app/Repositories/CostCentreRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\CostCentreRepositoryInterface;
use App\Models\CostCentre;
use Illuminate\Database\Eloquent\Collection;

class CostCentreRepository extends BaseRepository implements CostCentreRepositoryInterface
{
    protected function model(): string
    {
        return CostCentre::class;
    }

    public function findByCode(string $code): ?CostCentre
    {
        return $this->model->where('code', $code)->first();
    }

    public function findByDepartment(int $departmentId, bool $activeOnly = true): Collection
    {
        return $this->model
            ->where('department_id', $departmentId)
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->orderBy('code')
            ->get();
    }

    public function findWithCurrentBudget(int $departmentId): Collection
    {
        return $this->model
            ->with(['budgetAllocations' => fn ($q) => $q->where('fiscal_year', now()->year)])
            ->where('department_id', $departmentId)
            ->where('is_active', true)
            ->get();
    }
}
```

---

### `app/Repositories/StockItemRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\StockItemRepositoryInterface;
use App\Models\StockItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class StockItemRepository extends BaseRepository implements StockItemRepositoryInterface
{
    protected function model(): string
    {
        return StockItem::class;
    }

    public function findByStockCode(string $code): ?StockItem
    {
        return $this->model->where('stock_code', $code)->first();
    }

    public function findStocked(): Collection
    {
        return $this->model->stocked()->orderBy('stock_code')->get();
    }

    public function findOrderable(): Collection
    {
        return $this->model->orderable()->orderBy('stock_code')->get();
    }

    public function findBelowReorderLevel(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('stock_code')
            ->get();
    }

    public function adjustQuantity(int $id, float $delta): bool
    {
        return (bool) $this->model
            ->where('id', $id)
            ->increment('quantity_on_hand', $delta);
    }

    public function search(string $term, ?int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('stock_code', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('category', 'like', "%{$term}%");
            })
            ->orderBy('stock_code')
            ->paginate($perPage);
    }
}
```

---

### `app/Repositories/SupplierRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\SupplierRepositoryInterface;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    protected function model(): string
    {
        return Supplier::class;
    }

    public function findActive(): Collection
    {
        return $this->model->where('is_active', true)->orderBy('name')->get();
    }

    public function findZppaApproved(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->where('zppa_approved', true)
            ->orderBy('name')
            ->get();
    }

    public function search(string $term, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('contact_email', 'like', "%{$term}%")
                  ->orWhere('zppa_registration', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function getQuoteCount(int $supplierId): int
    {
        return $this->model->find($supplierId)?->quotes()->count() ?? 0;
    }
}
```

---

### `app/Repositories/PurchaseRequisitionRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\PurchaseRequisition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PurchaseRequisitionRepository extends BaseRepository implements PurchaseRequisitionRepositoryInterface
{
    protected function model(): string
    {
        return PurchaseRequisition::class;
    }

    public function findByReference(string $reference): ?PurchaseRequisition
    {
        return $this->model->where('reference_no', $reference)->first();
    }

    public function findWithFullTrail(int $id): ?PurchaseRequisition
    {
        return $this->model->with([
            'requester',
            'department',
            'costCentre.budgetAllocations' => fn ($q) => $q->where('fiscal_year', now()->year),
            'items.stockItem',
            'approvalRecords.actor',
            'supplierQuotes.supplier',
            'purchaseOrder.supplier',
            'purchaseOrder.items',
            'purchaseOrder.payment.vcApprover',
            'purchaseOrder.payment.bursarConfirmer',
            'attachments.uploader',
        ])->find($id);
    }

    public function findByRequester(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['department', 'costCentre'])
            ->where('requester_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findByDepartment(int $departmentId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['requester', 'costCentre'])
            ->where('department_id', $departmentId)
            ->latest()
            ->paginate($perPage);
    }

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['requester', 'department', 'costCentre'])
            ->when(
                is_array($status),
                fn ($q) => $q->whereIn('status', $status),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findPendingForUser(int $userId, string $role): LengthAwarePaginator
    {
        $statusMap = [
            'hod'                 => ['pending_hod'],
            'stores'              => ['pending_stores'],
            'bursar'              => ['pending_bursar'],
            'vc'                  => ['pending_vc_requisition', 'pending_vc_payment'],
            'procurement'         => ['pending_procurement'],
            'auditor'             => ['pending_audit'],
        ];

        $statuses = $statusMap[$role] ?? [];

        return $this->model
            ->with(['requester', 'department', 'costCentre'])
            ->when(
                $role === 'hod',
                // HOD only sees their own department's PRs
                fn ($q) => $q->whereHas('department', fn ($d) => $d->where('hod_user_id', $userId))
            )
            ->when(
                !empty($statuses),
                fn ($q) => $q->whereIn('status', $statuses)
            )
            ->latest()
            ->paginate(20);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return (bool) $this->model->where('id', $id)->update(['status' => $status]);
    }

    public function countByStatus(): array
    {
        return $this->model
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    public function findFlaggedServiceConsumed(): Collection
    {
        return $this->model
            ->with(['requester', 'department'])
            ->where('service_consumed_flag', true)
            ->whereNotIn('status', ['rejected', 'suspended', 'delivered'])
            ->latest()
            ->get();
    }

    public function search(
        ?string $reference = null,
        ?int $departmentId = null,
        ?int $requesterId = null,
        ?string $status = null,
        ?string $type = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return $this->model
            ->with(['requester', 'department', 'costCentre'])
            ->when($reference,    fn ($q) => $q->where('reference_no', 'like', "%{$reference}%"))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->when($requesterId,  fn ($q) => $q->where('requester_id', $requesterId))
            ->when($status,       fn ($q) => $q->where('status', $status))
            ->when($type,         fn ($q) => $q->where('type', $type))
            ->when($dateFrom,     fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,       fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->paginate($perPage);
    }
}
```

---

### `app/Repositories/PurchaseRequisitionItemRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\PurchaseRequisitionItemRepositoryInterface;
use App\Models\PurchaseRequisitionItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseRequisitionItemRepository extends BaseRepository implements PurchaseRequisitionItemRepositoryInterface
{
    protected function model(): string
    {
        return PurchaseRequisitionItem::class;
    }

    public function findByRequisition(int $prId): Collection
    {
        return $this->model
            ->with('stockItem')
            ->where('purchase_requisition_id', $prId)
            ->get();
    }

    public function syncItems(int $prId, array $items): Collection
    {
        return DB::transaction(function () use ($prId, $items) {
            $this->model->where('purchase_requisition_id', $prId)->delete();

            foreach ($items as $item) {
                $item['purchase_requisition_id'] = $prId;
                $item['total_price_estimated']   = $item['quantity'] * $item['unit_price_estimated'];
                $this->model->create($item);
            }

            return $this->findByRequisition($prId);
        });
    }

    public function recalculateTotal(int $itemId): PurchaseRequisitionItem
    {
        $item = $this->model->findOrFail($itemId);
        $item->update([
            'total_price_estimated' => $item->quantity * $item->unit_price_estimated,
        ]);

        return $item->fresh();
    }
}
```

---

### `app/Repositories/ApprovalRecordRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\ApprovalRecordRepositoryInterface;
use App\Models\ApprovalRecord;
use Illuminate\Database\Eloquent\Collection;

class ApprovalRecordRepository extends BaseRepository implements ApprovalRecordRepositoryInterface
{
    protected function model(): string
    {
        return ApprovalRecord::class;
    }

    public function findByRequisition(int $prId): Collection
    {
        return $this->model
            ->with('actor')
            ->where('purchase_requisition_id', $prId)
            ->orderBy('created_at')
            ->get();
    }

    public function findLatestForStage(int $prId, string $stage): ?ApprovalRecord
    {
        return $this->model
            ->with('actor')
            ->where('purchase_requisition_id', $prId)
            ->where('stage', $stage)
            ->latest('created_at')
            ->first();
    }

    public function stageCompleted(int $prId, string $stage): bool
    {
        return $this->model
            ->where('purchase_requisition_id', $prId)
            ->where('stage', $stage)
            ->exists();
    }

    public function getApproverMap(int $prId): array
    {
        return $this->model
            ->where('purchase_requisition_id', $prId)
            ->pluck('actor_id', 'stage')
            ->toArray();
    }
}
```

---

### `app/Repositories/SupplierQuoteRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\SupplierQuoteRepositoryInterface;
use App\Models\SupplierQuote;
use Illuminate\Database\Eloquent\Collection;

class SupplierQuoteRepository extends BaseRepository implements SupplierQuoteRepositoryInterface
{
    protected function model(): string
    {
        return SupplierQuote::class;
    }

    public function findByRequisition(int $prId): Collection
    {
        return $this->model
            ->with('supplier')
            ->where('purchase_requisition_id', $prId)
            ->orderBy('amount')
            ->get();
    }

    public function findResponded(int $prId): Collection
    {
        return $this->model
            ->with('supplier')
            ->where('purchase_requisition_id', $prId)
            ->whereNotNull('amount')
            ->orderBy('amount')
            ->get();
    }

    public function findNoResponse(int $prId): Collection
    {
        return $this->model
            ->with('supplier')
            ->where('purchase_requisition_id', $prId)
            ->whereNull('amount')
            ->where('supplier_contacted', true)
            ->get();
    }

    public function findLowestQuote(int $prId): ?SupplierQuote
    {
        return $this->model
            ->with('supplier')
            ->where('purchase_requisition_id', $prId)
            ->whereNotNull('amount')
            ->orderBy('amount')
            ->first();
    }

    public function allQuotesExceedThreshold(int $prId, float $threshold): bool
    {
        $respondedCount = $this->model
            ->where('purchase_requisition_id', $prId)
            ->whereNotNull('amount')
            ->count();

        if ($respondedCount === 0) {
            return false;
        }

        $exceedingCount = $this->model
            ->where('purchase_requisition_id', $prId)
            ->whereNotNull('amount')
            ->where('amount', '>', $threshold)
            ->count();

        return $exceedingCount === $respondedCount;
    }
}
```

---

### `app/Repositories/PurchaseOrderRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Models\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PurchaseOrderRepository extends BaseRepository implements PurchaseOrderRepositoryInterface
{
    protected function model(): string
    {
        return PurchaseOrder::class;
    }

    public function findByPoNumber(string $poNumber): ?PurchaseOrder
    {
        return $this->model->where('po_number', $poNumber)->with(['purchaseRequisition', 'supplier'])->first();
    }

    public function findByRequisition(int $prId): ?PurchaseOrder
    {
        return $this->model
            ->with(['supplier', 'items', 'payment'])
            ->where('purchase_requisition_id', $prId)
            ->first();
    }

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['purchaseRequisition.department', 'supplier'])
            ->when(
                is_array($status),
                fn ($q) => $q->whereIn('status', $status),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findUndelivered(): Collection
    {
        return $this->model
            ->with(['purchaseRequisition.department', 'supplier'])
            ->whereIn('status', ['issued', 'partially_delivered'])
            ->orderBy('issued_date')
            ->get();
    }

    public function findForReport(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return $this->model
            ->with(['purchaseRequisition.requester', 'purchaseRequisition.department', 'supplier', 'payment'])
            ->when($status,   fn ($q) => $q->where('status', $status))
            ->when($dateFrom, fn ($q) => $q->whereDate('issued_date', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('issued_date', '<=', $dateTo))
            ->latest('issued_date')
            ->paginate($perPage);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return (bool) $this->model->where('id', $id)->update(['status' => $status]);
    }
}
```

---

### `app/Repositories/PaymentRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\PaymentRepositoryInterface;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    protected function model(): string
    {
        return Payment::class;
    }

    public function findByPurchaseOrder(int $poId): ?Payment
    {
        return $this->model
            ->with(['vcApprover', 'bursarConfirmer', 'purchaseOrder.supplier'])
            ->where('purchase_order_id', $poId)
            ->first();
    }

    public function findByStatus(string|array $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition.department', 'purchaseOrder.supplier'])
            ->when(
                is_array($status),
                fn ($q) => $q->whereIn('status', $status),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findPendingVcApproval(): Collection
    {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition', 'purchaseOrder.supplier'])
            ->where('status', 'pending')
            ->whereNull('vc_approved_at')
            ->get();
    }

    public function findPendingBursarConfirmation(): Collection
    {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition', 'purchaseOrder.supplier'])
            ->where('status', 'pending')
            ->whereNotNull('vc_approved_at')
            ->whereNull('bursar_confirmed_at')
            ->get();
    }

    public function findDelayed(): Collection
    {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition', 'purchaseOrder.supplier'])
            ->whereNotNull('delay_comment')
            ->where('status', 'pending')
            ->get();
    }

    public function findForReport(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return $this->model
            ->with(['purchaseOrder.purchaseRequisition.requester', 'purchaseOrder.supplier', 'vcApprover', 'bursarConfirmer'])
            ->when($status,   fn ($q) => $q->where('status', $status))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->paginate($perPage);
    }
}
```

---

### `app/Repositories/AttachmentRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\AttachmentRepositoryInterface;
use App\Models\Attachment;
use Illuminate\Database\Eloquent\Collection;

class AttachmentRepository extends BaseRepository implements AttachmentRepositoryInterface
{
    protected function model(): string
    {
        return Attachment::class;
    }

    public function findByRequisition(int $prId): Collection
    {
        return $this->model
            ->with('uploader')
            ->where('purchase_requisition_id', $prId)
            ->orderBy('created_at')
            ->get();
    }

    public function findByType(int $prId, string $type): Collection
    {
        return $this->model
            ->with('uploader')
            ->where('purchase_requisition_id', $prId)
            ->where('attachment_type', $type)
            ->get();
    }

    public function hasType(int $prId, string $type): bool
    {
        return $this->model
            ->where('purchase_requisition_id', $prId)
            ->where('attachment_type', $type)
            ->exists();
    }
}
```

---

### `app/Repositories/BudgetAllocationRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\BudgetAllocationRepositoryInterface;
use App\Models\BudgetAllocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BudgetAllocationRepository extends BaseRepository implements BudgetAllocationRepositoryInterface
{
    protected function model(): string
    {
        return BudgetAllocation::class;
    }

    public function findByCostCentreAndYear(int $costCentreId, int $fiscalYear): ?BudgetAllocation
    {
        return $this->model
            ->where('cost_centre_id', $costCentreId)
            ->where('fiscal_year', $fiscalYear)
            ->first();
    }

    public function findCurrentYear(int $costCentreId): ?BudgetAllocation
    {
        return $this->findByCostCentreAndYear($costCentreId, now()->year);
    }

    public function findAllForYear(int $fiscalYear): Collection
    {
        return $this->model
            ->with(['costCentre.department'])
            ->where('fiscal_year', $fiscalYear)
            ->orderBy('cost_centre_id')
            ->get();
    }

    public function incrementCommitted(int $id, float $amount): bool
    {
        return (bool) DB::transaction(function () use ($id, $amount) {
            return $this->model
                ->lockForUpdate()
                ->where('id', $id)
                ->increment('committed_amount', $amount);
        });
    }

    public function decrementCommitted(int $id, float $amount): bool
    {
        return (bool) DB::transaction(function () use ($id, $amount) {
            return $this->model
                ->lockForUpdate()
                ->where('id', $id)
                ->decrement('committed_amount', $amount);
        });
    }

    public function incrementSpent(int $id, float $amount): bool
    {
        return (bool) DB::transaction(function () use ($id, $amount) {
            return $this->model
                ->lockForUpdate()
                ->where('id', $id)
                ->increment('spent_amount', $amount);
        });
    }
}
```

---

### `app/Repositories/AuditLogRepository.php`

```php
<?php

namespace App\Repositories;

use App\Contracts\AuditLogRepositoryInterface;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AuditLogRepository extends BaseRepository implements AuditLogRepositoryInterface
{
    protected function model(): string
    {
        return AuditLog::class;
    }

    public function findForEntity(string $entityType, int $entityId): Collection
    {
        return $this->model
            ->with('actor')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('logged_at')
            ->get();
    }

    public function findByActor(int $actorId, int $perPage = 30): LengthAwarePaginator
    {
        return $this->model
            ->where('actor_id', $actorId)
            ->latest('logged_at')
            ->paginate($perPage);
    }

    public function log(
        string $entityType,
        int $entityId,
        string $action,
        array $oldValues = [],
        array $newValues = [],
        ?int $actorId = null,
        ?string $ip = null
    ): AuditLog {
        return $this->model->create([
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'action'      => $action,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'actor_id'    => $actorId ?? auth()->id(),
            'ip_address'  => $ip ?? request()->ip(),
            'logged_at'   => now(),
        ]);
    }
}
```

---

## PART C — SERVICES

---

### `app/Services/BudgetService.php`

```php
<?php

namespace App\Services;

use App\Contracts\BudgetAllocationRepositoryInterface;
use App\Models\BudgetAllocation;
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
     */
    public function recordSpend(int $costCentreId, float $amount): void
    {
        DB::transaction(function () use ($costCentreId, $amount) {
            $allocation = $this->getCurrentAllocation($costCentreId);
            $this->budgetRepo->decrementCommitted($allocation->id, $amount);
            $this->budgetRepo->incrementSpent($allocation->id, $amount);
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
```

---

### `app/Services/AuditComplianceService.php`

```php
<?php

namespace App\Services;

use App\Contracts\ApprovalRecordRepositoryInterface;
use App\Contracts\AttachmentRepositoryInterface;
use App\Contracts\AuditLogRepositoryInterface;
use App\Contracts\SupplierQuoteRepositoryInterface;
use App\Models\PurchaseRequisition;

class AuditComplianceService
{
    public function __construct(
        private readonly SupplierQuoteRepositoryInterface  $quoteRepo,
        private readonly AttachmentRepositoryInterface     $attachmentRepo,
        private readonly ApprovalRecordRepositoryInterface $approvalRepo,
        private readonly AuditLogRepositoryInterface       $auditLogRepo,
    ) {}

    /**
     * Run all compliance checks on a PR and return a structured result.
     *
     * @return array{
     *   passed: bool,
     *   flags: array<string, array{passed: bool, message: string}>
     * }
     */
    public function runChecks(PurchaseRequisition $pr): array
    {
        $flags = [
            'quote_count'          => $this->checkQuoteCount($pr),
            'missing_contact_log'  => $this->checkMissingContactLogs($pr),
            'committee_evidence'   => $this->checkCommitteeEvidence($pr),
            'approver_validity'    => $this->checkApproverValidity($pr),
            'service_consumed'     => $this->checkServiceConsumed($pr),
        ];

        $passed = collect($flags)->every(fn ($f) => $f['passed']);

        return compact('passed', 'flags');
    }

    /**
     * PRs above the threshold must have at least min_quotes_required responded quotes.
     */
    public function checkQuoteCount(PurchaseRequisition $pr): array
    {
        $required  = config('procurement.min_quotes_required', 3);
        $threshold = config('procurement.quote_threshold', 10000);

        if ($pr->totalEstimated() < $threshold) {
            return ['passed' => true, 'message' => 'Below quote threshold — no minimum required.'];
        }

        $responded = $this->quoteRepo->findResponded($pr->id)->count();

        if ($responded >= $required) {
            return ['passed' => true, 'message' => "Required {$required} quotes; {$responded} received."];
        }

        return [
            'passed'  => false,
            'message' => "Only {$responded} of {$required} required quotes have been received.",
        ];
    }

    /**
     * Every supplier that was contacted but returned no quote must have contact evidence logged.
     */
    public function checkMissingContactLogs(PurchaseRequisition $pr): array
    {
        $noResponse = $this->quoteRepo->findNoResponse($pr->id);

        $missingEvidence = $noResponse->filter(
            fn ($q) => blank($q->contact_evidence_path)
        );

        if ($missingEvidence->isEmpty()) {
            return ['passed' => true, 'message' => 'All non-responding suppliers have contact evidence on file.'];
        }

        $names = $missingEvidence->map(fn ($q) => $q->supplier->name)->join(', ');

        return [
            'passed'  => false,
            'message' => "Missing contact evidence for: {$names}.",
        ];
    }

    /**
     * If all responded quotes exceed the committee threshold, procurement committee
     * minutes must be attached.
     */
    public function checkCommitteeEvidence(PurchaseRequisition $pr): array
    {
        $committeeThreshold = config('procurement.committee_threshold', 50000);

        $allExceed = $this->quoteRepo->allQuotesExceedThreshold($pr->id, $committeeThreshold);

        if (! $allExceed) {
            return ['passed' => true, 'message' => 'Committee evidence not required.'];
        }

        $hasMinutes = $this->attachmentRepo->hasType($pr->id, 'committee_minutes');

        return $hasMinutes
            ? ['passed' => true,  'message' => 'Procurement committee minutes are on file.']
            : ['passed' => false, 'message' => 'All quotes exceed the threshold — committee minutes must be attached.'];
    }

    /**
     * Verify each stage was approved by a user carrying the correct role.
     */
    public function checkApproverValidity(PurchaseRequisition $pr): array
    {
        $stageRoleMap = [
            'hod'              => 'hod',
            'stores'           => 'stores',
            'bursar'           => 'bursar',
            'vc_requisition'   => 'vc',
            'vc_payment'       => 'vc',
        ];

        $violations = [];

        foreach ($stageRoleMap as $stage => $expectedRole) {
            $record = $this->approvalRepo->findLatestForStage($pr->id, $stage);

            if (! $record) {
                continue; // Stage not yet reached
            }

            if (! $record->actor->hasRole($expectedRole)) {
                $violations[] = "Stage '{$stage}' was actioned by {$record->actor->name}, who does not hold the '{$expectedRole}' role.";
            }
        }

        return empty($violations)
            ? ['passed' => true,  'message' => 'All stages approved by authorised actors.']
            : ['passed' => false, 'message' => implode(' | ', $violations)];
    }

    /**
     * Service-type PRs: flag if service was consumed before the PR was raised.
     */
    public function checkServiceConsumed(PurchaseRequisition $pr): array
    {
        if ($pr->type !== 'service') {
            return ['passed' => true, 'message' => 'Not a service PR — check not applicable.'];
        }

        return $pr->service_consumed_flag
            ? ['passed' => false, 'message' => 'This service was consumed before the PR was raised.']
            : ['passed' => true,  'message' => 'Service was booked in advance — no flag.'];
    }

    /**
     * Write an immutable audit log entry.
     */
    public function log(
        string $entityType,
        int $entityId,
        string $action,
        array $old = [],
        array $new = []
    ): void {
        $this->auditLogRepo->log($entityType, $entityId, $action, $old, $new);
    }
}
```

---

### `app/Services/WorkflowService.php`

```php
<?php

namespace App\Services;

use App\Contracts\ApprovalRecordRepositoryInterface;
use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\ApprovalRecord;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkflowService
{
    /**
     * Valid status transitions: currentStatus => allowedNextStatuses[].
     * This is the single source of truth for what can follow what.
     */
    private const TRANSITIONS = [
        'draft'                  => ['pending_hod'],
        'pending_hod'            => ['pending_stores', 'rejected'],
        'pending_stores'         => ['issued_from_stores', 'pending_bursar'],
        'pending_bursar'         => ['pending_vc_requisition', 'rejected'],
        'pending_vc_requisition' => ['pending_procurement', 'rejected'],
        'pending_procurement'    => ['pending_audit'],
        'pending_audit'          => ['pending_vc_payment'],
        'pending_vc_payment'     => ['pending_payment', 'suspended'],
        'pending_payment'        => ['paid'],
        'paid'                   => ['delivered'],
    ];

    public function __construct(
        private readonly PurchaseRequisitionRepositoryInterface $prRepo,
        private readonly ApprovalRecordRepositoryInterface      $approvalRepo,
        private readonly AuditComplianceService                 $complianceService,
    ) {}

    // ── Transition guards ──────────────────────────────────────────────────

    public function canTransitionTo(PurchaseRequisition $pr, string $targetStatus): bool
    {
        $allowed = self::TRANSITIONS[$pr->status] ?? [];

        return in_array($targetStatus, $allowed, true);
    }

    public function getAllowedTransitions(PurchaseRequisition $pr): array
    {
        return self::TRANSITIONS[$pr->status] ?? [];
    }

    // ── Stage processors ───────────────────────────────────────────────────

    /**
     * HOD approves or rejects the PR.
     */
    public function processHodDecision(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'approved' | 'rejected'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_hod');
        $this->assertRole($actor, 'hod');
        $this->requireCommentOnRejection($decision, $comment);

        $nextStatus = $decision === 'approved' ? 'pending_stores' : 'rejected';

        return $this->recordAndAdvance($pr, $actor, 'hod', $decision, $comment, $nextStatus);
    }

    /**
     * Stores officer checks stock.
     * Decision: 'issued' (in stock) | 'approved' (not in stock, proceed to Bursar).
     */
    public function processStoresCheck(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'issued' | 'approved'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_stores');
        $this->assertRole($actor, 'stores');

        $nextStatus = $decision === 'issued' ? 'issued_from_stores' : 'pending_bursar';

        return $this->recordAndAdvance($pr, $actor, 'stores', $decision, $comment, $nextStatus);
    }

    /**
     * Bursar confirms funds availability and commits (or refuses) to pay.
     */
    public function processBursarDecision(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'committed' | 'not_committed'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_bursar');
        $this->assertRole($actor, 'bursar');
        $this->requireCommentOnRejection($decision === 'not_committed' ? 'rejected' : 'approved', $comment);

        $nextStatus = $decision === 'committed' ? 'pending_vc_requisition' : 'rejected';

        return $this->recordAndAdvance($pr, $actor, 'bursar', $decision, $comment, $nextStatus);
    }

    /**
     * VC approves or rejects the requisition.
     */
    public function processVcRequisitionDecision(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'approved' | 'rejected'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_vc_requisition');
        $this->assertRole($actor, 'vc');
        $this->requireCommentOnRejection($decision, $comment);

        $nextStatus = $decision === 'approved' ? 'pending_procurement' : 'rejected';

        return $this->recordAndAdvance($pr, $actor, 'vc_requisition', $decision, $comment, $nextStatus);
    }

    /**
     * Auditor reviews compliance. Decision: 'noted' (pass) | 'rejected' (fail).
     */
    public function processAuditReview(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_audit');
        $this->assertRole($actor, 'auditor');

        // Run automated compliance checks and record the result
        $checks = $this->complianceService->runChecks($pr);

        if (! $checks['passed'] && $decision === 'noted') {
            throw new RuntimeException(
                'Compliance checks have outstanding failures. Audit cannot pass this PR: '
                . collect($checks['flags'])->where('passed', false)->map(fn ($f) => $f['message'])->join(' | ')
            );
        }

        $nextStatus = $decision === 'noted' ? 'pending_vc_payment' : 'rejected';

        return $this->recordAndAdvance($pr, $actor, 'audit', $decision, $comment, $nextStatus);
    }

    /**
     * VC approves or suspends payment.
     */
    public function processVcPaymentDecision(
        PurchaseRequisition $pr,
        User $actor,
        string $decision,   // 'approved' | 'suspended'
        ?string $comment = null
    ): ApprovalRecord {
        $this->assertStatus($pr, 'pending_vc_payment');
        $this->assertRole($actor, 'vc');
        $this->requireCommentOnRejection($decision === 'suspended' ? 'rejected' : 'approved', $comment);

        $nextStatus = $decision === 'approved' ? 'pending_payment' : 'suspended';

        return $this->recordAndAdvance($pr, $actor, 'vc_payment', $decision, $comment, $nextStatus);
    }

    // ── Internals ──────────────────────────────────────────────────────────

    private function recordAndAdvance(
        PurchaseRequisition $pr,
        User $actor,
        string $stage,
        string $decision,
        ?string $comment,
        string $nextStatus
    ): ApprovalRecord {
        return DB::transaction(function () use ($pr, $actor, $stage, $decision, $comment, $nextStatus) {
            $record = $this->approvalRepo->create([
                'purchase_requisition_id' => $pr->id,
                'actor_id'                => $actor->id,
                'stage'                   => $stage,
                'decision'                => $decision,
                'comment'                 => $comment,
                'acted_at'                => now(),
            ]);

            $this->prRepo->updateStatus($pr->id, $nextStatus);

            $this->complianceService->log(
                PurchaseRequisition::class,
                $pr->id,
                "stage_{$stage}_{$decision}",
                ['status' => $pr->status],
                ['status' => $nextStatus]
            );

            return $record;
        });
    }

    private function assertStatus(PurchaseRequisition $pr, string $expected): void
    {
        if ($pr->status !== $expected) {
            throw new RuntimeException(
                "Cannot process: PR #{$pr->reference_no} is in status '{$pr->status}', expected '{$expected}'."
            );
        }
    }

    private function assertRole(User $actor, string $role): void
    {
        if (! $actor->hasRole($role)) {
            throw new RuntimeException(
                "User {$actor->name} does not have the required '{$role}' role to perform this action."
            );
        }
    }

    private function requireCommentOnRejection(string $decision, ?string $comment): void
    {
        if ($decision === 'rejected' && blank($comment)) {
            throw new RuntimeException('A comment is required when rejecting a purchase requisition.');
        }
    }
}
```

---

### `app/Services/PurchaseRequisitionService.php`

```php
<?php

namespace App\Services;

use App\Contracts\PurchaseRequisitionItemRepositoryInterface;
use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseRequisitionService
{
    public function __construct(
        private readonly PurchaseRequisitionRepositoryInterface     $prRepo,
        private readonly PurchaseRequisitionItemRepositoryInterface $itemRepo,
        private readonly AuditComplianceService                     $auditService,
    ) {}

    /**
     * Create a new PR in 'draft' status and immediately submit it to the HOD.
     * $data keys: type, cost_centre_id, notes, items[]
     * Each item: description, stock_item_id?, quantity, unit_of_measure?, unit_price_estimated, category?
     */
    public function createAndSubmit(array $data, User $requester): PurchaseRequisition
    {
        return DB::transaction(function () use ($data, $requester) {
            $pr = $this->prRepo->create([
                'type'          => $data['type'],
                'requester_id'  => $requester->id,
                'department_id' => $requester->department_id,
                'cost_centre_id'=> $data['cost_centre_id'],
                'status'        => 'pending_hod',
                'notes'         => $data['notes'] ?? null,
                'service_consumed_flag' => $this->detectServiceConsumed($data),
            ]);

            $this->itemRepo->syncItems($pr->id, $data['items']);

            $this->auditService->log(PurchaseRequisition::class, $pr->id, 'created', [], [
                'reference_no' => $pr->reference_no,
                'type'         => $pr->type,
                'requester'    => $requester->name,
            ]);

            return $pr->fresh(['items', 'requester', 'department', 'costCentre']);
        });
    }

    /**
     * Update a PR that is still in 'draft' or 'pending_hod' status.
     * Once past HOD it is locked.
     */
    public function update(int $prId, array $data, User $actor): PurchaseRequisition
    {
        $pr = $this->prRepo->findById($prId);

        if (! in_array($pr->status, ['draft', 'pending_hod'], true)) {
            throw new RuntimeException('A PR can only be edited while in draft or pending HOD review.');
        }

        return DB::transaction(function () use ($pr, $data, $actor) {
            $old = $pr->only(['notes', 'cost_centre_id']);

            $this->prRepo->update($pr->id, array_filter([
                'cost_centre_id' => $data['cost_centre_id'] ?? null,
                'notes'          => $data['notes'] ?? null,
            ]));

            if (isset($data['items'])) {
                $this->itemRepo->syncItems($pr->id, $data['items']);
            }

            $this->auditService->log(PurchaseRequisition::class, $pr->id, 'updated', $old, $data);

            return $this->prRepo->findWithFullTrail($pr->id);
        });
    }

    /**
     * Load the full audit trail summary for the VC or admin view.
     */
    public function getSummary(int $prId): PurchaseRequisition
    {
        $pr = $this->prRepo->findWithFullTrail($prId);

        if (! $pr) {
            throw new RuntimeException("Purchase Requisition #{$prId} not found.");
        }

        return $pr;
    }

    /**
     * Detect whether a service PR was raised after consumption.
     * Looks for a 'service_date' in the items that is in the past relative to today.
     */
    private function detectServiceConsumed(array $data): bool
    {
        if ($data['type'] !== 'service') {
            return false;
        }

        $serviceDate = $data['service_date'] ?? null;

        return $serviceDate && now()->startOfDay()->gt($serviceDate);
    }
}
```

---

### `app/Services/StoresService.php`

```php
<?php

namespace App\Services;

use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Contracts\StockItemRepositoryInterface;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StoresService
{
    public function __construct(
        private readonly StockItemRepositoryInterface  $stockRepo,
        private readonly PurchaseOrderRepositoryInterface $poRepo,
        private readonly AuditComplianceService        $auditService,
    ) {}

    /**
     * Check whether every item on a PR is available in stock.
     * Returns ['in_stock' => true/false, 'details' => [...per item...]]
     */
    public function checkAvailability(PurchaseRequisition $pr): array
    {
        $pr->loadMissing('items.stockItem');

        $details = $pr->items->map(function ($item) {
            $stock    = $item->stockItem;
            $inStock  = $stock && $stock->is_stocked && $stock->quantity_on_hand >= $item->quantity;

            return [
                'item_id'            => $item->id,
                'description'        => $item->description,
                'requested_quantity' => $item->quantity,
                'available_quantity' => $stock?->quantity_on_hand ?? 0,
                'in_stock'           => $inStock,
            ];
        });

        return [
            'in_stock' => $details->every('in_stock'),
            'details'  => $details->toArray(),
        ];
    }

    /**
     * Issue stocked items from the stores and deduct quantities.
     * Called after Stores marks a PR as 'issued_from_stores'.
     */
    public function issueFromStock(PurchaseRequisition $pr, User $storesOfficer): void
    {
        $pr->loadMissing('items.stockItem');

        DB::transaction(function () use ($pr, $storesOfficer) {
            foreach ($pr->items as $item) {
                if (! $item->stockItem) {
                    throw new RuntimeException("Item '{$item->description}' has no linked stock record.");
                }

                if ($item->stockItem->quantity_on_hand < $item->quantity) {
                    throw new RuntimeException(
                        "Insufficient stock for '{$item->description}'. Available: {$item->stockItem->quantity_on_hand}."
                    );
                }

                $this->stockRepo->adjustQuantity($item->stock_item_id, -$item->quantity);
            }

            $this->auditService->log(
                PurchaseRequisition::class, $pr->id,
                'issued_from_stores',
                [],
                ['issued_by' => $storesOfficer->name, 'issued_at' => now()->toDateTimeString()]
            );
        });
    }

    /**
     * Confirm that procured goods have been received.
     * Updates the PO status to 'delivered' and adjusts stock if items are stocked.
     */
    public function confirmGoodsReceived(PurchaseOrder $po, User $storesOfficer, \Carbon\Carbon $receivedDate): void
    {
        DB::transaction(function () use ($po, $storesOfficer, $receivedDate) {
            $this->poRepo->update($po->id, [
                'status'               => 'delivered',
                'actual_delivery_date' => $receivedDate,
            ]);

            // If any PO items are stocked items, add them back to stock
            $po->loadMissing('items', 'purchaseRequisition.items.stockItem');

            foreach ($po->purchaseRequisition->items as $prItem) {
                if ($prItem->stockItem && $prItem->stockItem->is_stocked) {
                    $this->stockRepo->adjustQuantity($prItem->stock_item_id, $prItem->quantity);
                }
            }

            // Advance PR status
            $po->purchaseRequisition->update(['status' => 'delivered']);

            $this->auditService->log(
                PurchaseOrder::class, $po->id,
                'goods_received',
                ['status' => 'paid'],
                ['status' => 'delivered', 'received_by' => $storesOfficer->name, 'received_at' => $receivedDate->toDateString()]
            );
        });
    }
}
```

---

### `app/Services/ProcurementService.php`

```php
<?php

namespace App\Services;

use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Contracts\SupplierQuoteRepositoryInterface;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\SupplierQuote;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProcurementService
{
    public function __construct(
        private readonly SupplierQuoteRepositoryInterface $quoteRepo,
        private readonly PurchaseOrderRepositoryInterface $poRepo,
        private readonly AuditComplianceService           $auditService,
    ) {}

    /**
     * Add a supplier quote (with or without a responded amount) to a PR.
     */
    public function addQuote(PurchaseRequisition $pr, array $data): SupplierQuote
    {
        if ($pr->status !== 'pending_procurement') {
            throw new RuntimeException('Quotes can only be added when the PR is in procurement review.');
        }

        $quote = $this->quoteRepo->create([
            'purchase_requisition_id' => $pr->id,
            'supplier_id'             => $data['supplier_id'],
            'amount'                  => $data['amount'] ?? null,
            'received_date'           => $data['received_date'] ?? null,
            'supplier_contacted'      => $data['supplier_contacted'] ?? true,
            'contact_evidence_path'   => $data['contact_evidence_path'] ?? null,
            'notes'                   => $data['notes'] ?? null,
        ]);

        $this->auditService->log(PurchaseRequisition::class, $pr->id, 'quote_added', [], [
            'supplier_id' => $data['supplier_id'],
            'amount'      => $data['amount'] ?? 'no response',
        ]);

        return $quote;
    }

    /**
     * Return a structured quote analysis for the PR summary view.
     *
     * @return array{
     *   total_quotes: int,
     *   responded: int,
     *   no_response: int,
     *   lowest_quote: SupplierQuote|null,
     *   requires_three_quotes: bool,
     *   quote_requirement_met: bool,
     *   committee_required: bool,
     *   quotes: Collection
     * }
     */
    public function getQuoteAnalysis(PurchaseRequisition $pr): array
    {
        $all       = $this->quoteRepo->findByRequisition($pr->id);
        $responded = $this->quoteRepo->findResponded($pr->id);
        $lowest    = $this->quoteRepo->findLowestQuote($pr->id);

        $requiresThree = $pr->requiresThreeQuotes();
        $minRequired   = config('procurement.min_quotes_required', 3);

        $committeeThreshold = config('procurement.committee_threshold', 50000);
        $committeeRequired  = $this->quoteRepo->allQuotesExceedThreshold($pr->id, $committeeThreshold);

        return [
            'total_quotes'          => $all->count(),
            'responded'             => $responded->count(),
            'no_response'           => $all->count() - $responded->count(),
            'lowest_quote'          => $lowest,
            'requires_three_quotes' => $requiresThree,
            'quote_requirement_met' => ! $requiresThree || $responded->count() >= $minRequired,
            'committee_required'    => $committeeRequired,
            'quotes'                => $all,
        ];
    }

    /**
     * Create a Purchase Order for a PR against a chosen supplier.
     * This advances the PR to 'pending_vc_payment' via the audit step.
     */
    public function createPurchaseOrder(
        PurchaseRequisition $pr,
        int $supplierId,
        array $items,   // [['description', 'quantity', 'unit_of_measure', 'unit_price', 'total_price'], ...]
        User $actor
    ): PurchaseOrder {
        if ($pr->status !== 'pending_audit') {
            throw new RuntimeException('A Purchase Order can only be created after audit review.');
        }

        return DB::transaction(function () use ($pr, $supplierId, $items, $actor) {
            $totalValue = collect($items)->sum('total_price');

            $po = $this->poRepo->create([
                'purchase_requisition_id' => $pr->id,
                'supplier_id'             => $supplierId,
                'total_value'             => $totalValue,
                'status'                  => 'draft',
                'issued_date'             => now(),
            ]);

            foreach ($items as $item) {
                $po->items()->create($item);
            }

            $this->auditService->log(PurchaseOrder::class, $po->id, 'created', [], [
                'po_number'    => $po->po_number,
                'supplier_id'  => $supplierId,
                'total_value'  => $totalValue,
                'created_by'   => $actor->name,
            ]);

            return $po->fresh(['items', 'supplier']);
        });
    }

    /**
     * Mark a PO as issued (sent to supplier).
     */
    public function issuePurchaseOrder(PurchaseOrder $po, User $actor): PurchaseOrder
    {
        $this->poRepo->update($po->id, [
            'status'      => 'issued',
            'issued_date' => now(),
        ]);

        $this->auditService->log(PurchaseOrder::class, $po->id, 'issued', ['status' => 'draft'], ['status' => 'issued']);

        return $po->fresh();
    }
}
```

---

### `app/Services/PaymentService.php`

```php
<?php

namespace App\Services;

use App\Contracts\PaymentRepositoryInterface;
use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Models\Payment;
use App\Models\PurchaseOrder;
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

            // Move committed budget to spent
            $pr = $payment->purchaseOrder->purchaseRequisition;
            $this->budgetService->recordSpend($pr->cost_centre_id, $payment->amount);

            // Advance the PR status
            $pr->update(['status' => 'paid']);

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
```

---

### `app/Services/AttachmentService.php`

```php
<?php

namespace App\Services;

use App\Contracts\AttachmentRepositoryInterface;
use App\Models\Attachment;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AttachmentService
{
    // Permitted MIME types
    private const ALLOWED_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
    ];

    // Max size in bytes (5 MB)
    private const MAX_SIZE = 5 * 1024 * 1024;

    public function __construct(
        private readonly AttachmentRepositoryInterface $attachmentRepo,
        private readonly AuditComplianceService        $auditService,
    ) {}

    /**
     * Validate, store and record an attachment.
     *
     * @param  string  $type  e.g. 'memo' | 'quote_evidence' | 'committee_minutes' | 'specification'
     */
    public function upload(
        PurchaseRequisition $pr,
        UploadedFile $file,
        string $type,
        User $uploader
    ): Attachment {
        $this->validateFile($file);

        $path = $file->store(
            "procurement/pr-{$pr->id}/{$type}",
            'private'
        );

        $attachment = $this->attachmentRepo->create([
            'purchase_requisition_id' => $pr->id,
            'uploaded_by'             => $uploader->id,
            'file_name'               => $file->getClientOriginalName(),
            'file_path'               => $path,
            'mime_type'               => $file->getMimeType(),
            'file_size'               => $file->getSize(),
            'attachment_type'         => $type,
        ]);

        $this->auditService->log(PurchaseRequisition::class, $pr->id, 'attachment_uploaded', [], [
            'attachment_id' => $attachment->id,
            'file_name'     => $attachment->file_name,
            'type'          => $type,
            'uploaded_by'   => $uploader->name,
        ]);

        return $attachment;
    }

    /**
     * Delete an attachment and remove the file from storage.
     */
    public function delete(int $attachmentId, User $actor): bool
    {
        $attachment = $this->attachmentRepo->findById($attachmentId);

        if (! $attachment) {
            throw new RuntimeException("Attachment #{$attachmentId} not found.");
        }

        Storage::disk('private')->delete($attachment->file_path);

        $this->auditService->log(
            PurchaseRequisition::class,
            $attachment->purchase_requisition_id,
            'attachment_deleted',
            ['file_name' => $attachment->file_name],
            ['deleted_by' => $actor->name]
        );

        return $this->attachmentRepo->delete($attachmentId);
    }

    /**
     * Return a temporary signed URL for downloading an attachment.
     */
    public function temporaryUrl(Attachment $attachment, int $minutes = 5): string
    {
        return Storage::disk('private')->temporaryUrl($attachment->file_path, now()->addMinutes($minutes));
    }

    private function validateFile(UploadedFile $file): void
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw new RuntimeException('File type not permitted. Allowed: PDF, Word documents, JPEG, PNG.');
        }

        if ($file->getSize() > self::MAX_SIZE) {
            throw new RuntimeException('File exceeds the maximum allowed size of 5 MB.');
        }
    }
}
```

---

### `app/Services/ReportService.php`

```php
<?php

namespace App\Services;

use App\Contracts\BudgetAllocationRepositoryInterface;
use App\Contracts\PaymentRepositoryInterface;
use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Contracts\SupplierQuoteRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    public function __construct(
        private readonly PurchaseRequisitionRepositoryInterface $prRepo,
        private readonly PurchaseOrderRepositoryInterface       $poRepo,
        private readonly PaymentRepositoryInterface             $paymentRepo,
        private readonly BudgetAllocationRepositoryInterface    $budgetRepo,
        private readonly SupplierQuoteRepositoryInterface       $quoteRepo,
    ) {}

    /** Commitment report: all budget allocations for a fiscal year. */
    public function commitmentReport(int $fiscalYear): Collection
    {
        return $this->budgetRepo->findAllForYear($fiscalYear);
    }

    /** All approved (VC-approved) POs — the 'Approved Orders' report. */
    public function approvedOrdersReport(?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->poRepo->findForReport('issued', $dateFrom, $dateTo, $perPage);
    }

    /** All POs whose associated payment is marked 'paid'. */
    public function paidOrdersReport(?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paymentRepo->findForReport('paid', $dateFrom, $dateTo, $perPage);
    }

    /** POs with a payment in 'pending' status. */
    public function pendingOrdersReport(?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->paymentRepo->findForReport('pending', $dateFrom, $dateTo, $perPage);
    }

    /** POs where the service has been received (status = delivered, type = service). */
    public function servicedOrdersReport(?string $dateFrom = null, ?string $dateTo = null, int $perPage = 20): LengthAwarePaginator
    {
        return $this->poRepo->findForReport('delivered', $dateFrom, $dateTo, $perPage);
    }

    /** POs issued but not yet confirmed as delivered. */
    public function undeliveredOrdersReport(): Collection
    {
        return $this->poRepo->findUndelivered();
    }

    /** All PRs with outstanding compliance flags. */
    public function complianceExceptionsReport(int $perPage = 20): LengthAwarePaginator
    {
        return $this->prRepo->findByStatus(
            ['pending_procurement', 'pending_audit'],
            $perPage
        );
    }

    /** Dashboard counts for the VC and admin summary panel. */
    public function dashboardCounts(): array
    {
        return $this->prRepo->countByStatus();
    }
}
```

---

## PART D — SERVICE PROVIDER & BINDINGS

---

### `app/Providers/RepositoryServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Contracts\ApprovalRecordRepositoryInterface;
use App\Contracts\AttachmentRepositoryInterface;
use App\Contracts\AuditLogRepositoryInterface;
use App\Contracts\BudgetAllocationRepositoryInterface;
use App\Contracts\CostCentreRepositoryInterface;
use App\Contracts\DepartmentRepositoryInterface;
use App\Contracts\PaymentRepositoryInterface;
use App\Contracts\PurchaseOrderRepositoryInterface;
use App\Contracts\PurchaseRequisitionItemRepositoryInterface;
use App\Contracts\PurchaseRequisitionRepositoryInterface;
use App\Contracts\StockItemRepositoryInterface;
use App\Contracts\SupplierQuoteRepositoryInterface;
use App\Contracts\SupplierRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\ApprovalRecordRepository;
use App\Repositories\AttachmentRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\BudgetAllocationRepository;
use App\Repositories\CostCentreRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Repositories\PurchaseRequisitionItemRepository;
use App\Repositories\PurchaseRequisitionRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\SupplierQuoteRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Interface => Concrete mapping.
     * Add new bindings here as the system grows.
     */
    private array $bindings = [
        UserRepositoryInterface::class                   => UserRepository::class,
        DepartmentRepositoryInterface::class             => DepartmentRepository::class,
        CostCentreRepositoryInterface::class             => CostCentreRepository::class,
        StockItemRepositoryInterface::class              => StockItemRepository::class,
        SupplierRepositoryInterface::class               => SupplierRepository::class,
        PurchaseRequisitionRepositoryInterface::class    => PurchaseRequisitionRepository::class,
        PurchaseRequisitionItemRepositoryInterface::class=> PurchaseRequisitionItemRepository::class,
        ApprovalRecordRepositoryInterface::class         => ApprovalRecordRepository::class,
        SupplierQuoteRepositoryInterface::class          => SupplierQuoteRepository::class,
        PurchaseOrderRepositoryInterface::class          => PurchaseOrderRepository::class,
        PaymentRepositoryInterface::class                => PaymentRepository::class,
        AttachmentRepositoryInterface::class             => AttachmentRepository::class,
        BudgetAllocationRepositoryInterface::class       => BudgetAllocationRepository::class,
        AuditLogRepositoryInterface::class               => AuditLogRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }

    public function boot(): void
    {
        //
    }
}
```

---

### Register the provider in `bootstrap/providers.php`

Open `bootstrap/providers.php` and add the provider:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,   // ← add this line
];
```

---

## STEP — Verify wiring

Run the following to confirm the container resolves bindings without errors:

```bash
php artisan tinker
```

```php
// Resolve each interface through the container
app(App\Contracts\PurchaseRequisitionRepositoryInterface::class);
app(App\Contracts\AuditLogRepositoryInterface::class);

// Resolve a service (its constructor dependencies should auto-inject)
app(App\Services\WorkflowService::class);
app(App\Services\PaymentService::class);
app(App\Services\ReportService::class);

// All should return instantiated objects with no errors
```

Exit tinker with `exit`.

---

## ✅ Phase 2 Complete

**What is now in place:**

| Layer | Files | Responsibility |
|---|---|---|
| Contracts | 15 interface files | Define data-access contracts; no implementation detail |
| Repositories | 15 concrete classes | All DB queries behind the interface; zero business logic |
| Services | 9 service classes | All business rules; depend only on interfaces, not concrete classes |
| Provider | 1 service provider | Single place to swap any repository for a mock or alternative driver |

**SOLID compliance:**
- **S** — Each class has one reason to change (e.g. `WorkflowService` only changes when workflow rules change)
- **O** — New workflow stages = new method; existing code untouched
- **L** — All concrete repositories are substitutable for their interface
- **I** — Interfaces are narrow and domain-specific; `BaseRepositoryInterface` is extended only where needed
- **D** — Services depend on interfaces injected through the container, never on concrete classes directly

**Next phase:** Form Requests, Controllers (API or web), and route definitions.
