# Procurement System — Build Instructions
## Phase 1: Models & Migrations

> **Agent instructions:** Execute each step in order. Do not skip steps.
> Run all commands from the Laravel project root. Confirm each command succeeds before proceeding.

---

## STEP 1 — Install Spatie Laravel Permission

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Open `config/permission.php` and confirm the `teams` option is `false` (default is fine).

---

## STEP 2 — Modify the default `users` table migration

Open `database/migrations/0001_01_01_000000_create_users_table.php`
Replace the `up()` method's schema with:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
    $table->boolean('is_active')->default(true);
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
});
```

> **Note:** `departments` table must exist before `users`. The migration for departments is created in Step 3 and must be given an earlier timestamp.

---

## STEP 3 — Create all migrations

Run the following commands in order. Each creates one migration file you will then fill in.

```bash
php artisan make:migration create_departments_table
php artisan make:migration create_cost_centres_table
php artisan make:migration create_stock_items_table
php artisan make:migration create_suppliers_table
php artisan make:migration create_purchase_requisitions_table
php artisan make:migration create_purchase_requisition_items_table
php artisan make:migration create_approval_records_table
php artisan make:migration create_supplier_quotes_table
php artisan make:migration create_purchase_orders_table
php artisan make:migration create_purchase_order_items_table
php artisan make:migration create_payments_table
php artisan make:migration create_attachments_table
php artisan make:migration create_budget_allocations_table
php artisan make:migration create_audit_logs_table
```

---

## STEP 4 — Fill in each migration

Open each migration file created above and replace the `up()` method with the schema below.

---

### 4.1 — `create_departments_table`

```php
public function up(): void
{
    Schema::create('departments', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('code')->unique();
        // hod_user_id added after users table exists — see migration 4.3
        $table->unsignedBigInteger('hod_user_id')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::dropIfExists('departments');
}
```

---

### 4.2 — `create_cost_centres_table`

```php
public function up(): void
{
    Schema::create('cost_centres', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('name');
        $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::dropIfExists('cost_centres');
}
```

---

### 4.3 — Add `hod_user_id` foreign key to departments (after users exists)

Create an additional migration to add the FK now that users table exists:

```bash
php artisan make:migration add_hod_user_id_foreign_to_departments_table
```

```php
public function up(): void
{
    Schema::table('departments', function (Blueprint $table) {
        $table->foreign('hod_user_id')
              ->references('id')
              ->on('users')
              ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('departments', function (Blueprint $table) {
        $table->dropForeign(['hod_user_id']);
    });
}
```

---

### 4.4 — `create_stock_items_table`

```php
public function up(): void
{
    Schema::create('stock_items', function (Blueprint $table) {
        $table->id();
        $table->string('stock_code')->unique();
        $table->string('description');
        $table->string('category')->nullable();
        // is_stocked = true means users CANNOT order this directly (must go through stores)
        $table->boolean('is_stocked')->default(false);
        $table->decimal('quantity_on_hand', 12, 2)->default(0);
        $table->decimal('reorder_level', 12, 2)->default(0);
        $table->string('unit_of_measure')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::dropIfExists('stock_items');
}
```

---

### 4.5 — `create_suppliers_table`

```php
public function up(): void
{
    Schema::create('suppliers', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('contact_person')->nullable();
        $table->string('contact_email')->nullable();
        $table->string('contact_phone')->nullable();
        $table->string('zppa_registration')->nullable()->comment('ZPPA supplier registration number');
        $table->boolean('zppa_approved')->default(false);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::dropIfExists('suppliers');
}
```

---

### 4.6 — `create_purchase_requisitions_table`

```php
public function up(): void
{
    Schema::create('purchase_requisitions', function (Blueprint $table) {
        $table->id();
        $table->string('reference_no')->unique()->comment('System-generated unique reference');
        $table->enum('type', ['product', 'service']);
        $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
        $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
        $table->foreignId('cost_centre_id')->constrained('cost_centres')->restrictOnDelete();
        $table->enum('status', [
            'draft',
            'pending_hod',
            'pending_stores',
            'issued_from_stores',
            'pending_bursar',
            'pending_vc_requisition',
            'pending_procurement',
            'pending_audit',
            'pending_vc_payment',
            'pending_payment',
            'paid',
            'delivered',
            'rejected',
            'suspended',
        ])->default('draft');
        // Flag: service was consumed BEFORE the PR was raised
        $table->boolean('service_consumed_flag')->default(false);
        $table->text('notes')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::dropIfExists('purchase_requisitions');
}
```

---

### 4.7 — `create_purchase_requisition_items_table`

```php
public function up(): void
{
    Schema::create('purchase_requisition_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_requisition_id')
              ->constrained('purchase_requisitions')
              ->cascadeOnDelete();
        // nullable: items without a stock code (free-text services)
        $table->foreignId('stock_item_id')->nullable()->constrained('stock_items')->nullOnDelete();
        $table->string('description');
        $table->string('category')->nullable();
        $table->decimal('quantity', 12, 2);
        $table->string('unit_of_measure')->nullable();
        // Estimated price comes from the budget allocation for the cost centre
        $table->decimal('unit_price_estimated', 15, 2)->default(0);
        $table->decimal('total_price_estimated', 15, 2)->default(0);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('purchase_requisition_items');
}
```

---

### 4.8 — `create_approval_records_table`

```php
public function up(): void
{
    Schema::create('approval_records', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_requisition_id')
              ->constrained('purchase_requisitions')
              ->cascadeOnDelete();
        $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
        $table->string('stage')->comment('hod | stores | bursar | vc_requisition | audit | vc_payment');
        $table->enum('decision', ['approved', 'rejected', 'committed', 'not_committed', 'issued', 'suspended', 'noted']);
        $table->text('comment')->nullable();
        $table->timestamp('acted_at')->useCurrent();
        // Immutable — no updated_at
        $table->timestamp('created_at')->useCurrent();
    });
}

public function down(): void
{
    Schema::dropIfExists('approval_records');
}
```

---

### 4.9 — `create_supplier_quotes_table`

```php
public function up(): void
{
    Schema::create('supplier_quotes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_requisition_id')
              ->constrained('purchase_requisitions')
              ->cascadeOnDelete();
        $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
        $table->decimal('amount', 15, 2)->nullable()->comment('Null if supplier did not respond');
        $table->date('received_date')->nullable();
        // Was the supplier formally contacted even if no quote came back?
        $table->boolean('supplier_contacted')->default(false);
        // Path to email copy / call log evidencing contact attempt
        $table->string('contact_evidence_path')->nullable();
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('supplier_quotes');
}
```

---

### 4.10 — `create_purchase_orders_table`

```php
public function up(): void
{
    Schema::create('purchase_orders', function (Blueprint $table) {
        $table->id();
        $table->string('po_number')->unique();
        $table->foreignId('purchase_requisition_id')
              ->constrained('purchase_requisitions')
              ->restrictOnDelete();
        $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
        $table->decimal('total_value', 15, 2)->default(0);
        $table->enum('status', [
            'draft',
            'issued',
            'partially_delivered',
            'delivered',
            'cancelled',
        ])->default('draft');
        $table->date('issued_date')->nullable();
        $table->date('expected_delivery_date')->nullable();
        $table->date('actual_delivery_date')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::dropIfExists('purchase_orders');
}
```

---

### 4.11 — `create_purchase_order_items_table`

```php
public function up(): void
{
    Schema::create('purchase_order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_order_id')
              ->constrained('purchase_orders')
              ->cascadeOnDelete();
        $table->string('description');
        $table->decimal('quantity', 12, 2);
        $table->string('unit_of_measure')->nullable();
        $table->decimal('unit_price', 15, 2);
        $table->decimal('total_price', 15, 2);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('purchase_order_items');
}
```

---

### 4.12 — `create_payments_table`

```php
public function up(): void
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_order_id')
              ->constrained('purchase_orders')
              ->restrictOnDelete();
        $table->decimal('amount', 15, 2);
        $table->enum('status', ['pending', 'paid', 'suspended'])->default('pending');
        // VC must approve payment before Bursar can release funds
        $table->foreignId('vc_approved_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('vc_approved_at')->nullable();
        // Bursar who confirmed funds were available
        $table->foreignId('bursar_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('bursar_confirmed_at')->nullable();
        $table->timestamp('paid_at')->nullable();
        // Mandatory comment explaining any payment delay
        $table->text('delay_comment')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('payments');
}
```

---

### 4.13 — `create_attachments_table`

```php
public function up(): void
{
    Schema::create('attachments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purchase_requisition_id')
              ->constrained('purchase_requisitions')
              ->cascadeOnDelete();
        $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
        $table->string('file_name');
        $table->string('file_path');
        $table->string('mime_type')->nullable();
        $table->unsignedBigInteger('file_size')->nullable()->comment('Size in bytes');
        // e.g. memo, quote_evidence, committee_minutes, procurement_committee
        $table->string('attachment_type')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('attachments');
}
```

---

### 4.14 — `create_budget_allocations_table`

```php
public function up(): void
{
    Schema::create('budget_allocations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cost_centre_id')->constrained('cost_centres')->cascadeOnDelete();
        $table->unsignedSmallInteger('fiscal_year');
        $table->decimal('allocated_amount', 15, 2)->default(0);
        $table->decimal('committed_amount', 15, 2)->default(0)->comment('Amounts Bursar has committed but not yet paid');
        $table->decimal('spent_amount', 15, 2)->default(0)->comment('Amounts actually paid');
        $table->timestamps();
        $table->unique(['cost_centre_id', 'fiscal_year']);
    });
}

public function down(): void
{
    Schema::dropIfExists('budget_allocations');
}
```

---

### 4.15 — `create_audit_logs_table`

```php
public function up(): void
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->string('entity_type')->comment('Model class e.g. PurchaseRequisition');
        $table->unsignedBigInteger('entity_id');
        $table->string('action')->comment('created | updated | status_changed | approved | rejected | deleted');
        $table->json('old_values')->nullable();
        $table->json('new_values')->nullable();
        $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
        $table->string('ip_address', 45)->nullable();
        // Immutable — no updated_at
        $table->timestamp('logged_at')->useCurrent();

        $table->index(['entity_type', 'entity_id']);
        $table->index('actor_id');
        $table->index('logged_at');
    });
}

public function down(): void
{
    Schema::dropIfExists('audit_logs');
}
```

---

## STEP 5 — Run all migrations

```bash
php artisan migrate
```

Confirm all 16+ tables created with no errors before continuing.

---

## STEP 6 — Create all Models

```bash
php artisan make:model Department
php artisan make:model CostCentre
php artisan make:model StockItem
php artisan make:model Supplier
php artisan make:model PurchaseRequisition
php artisan make:model PurchaseRequisitionItem
php artisan make:model ApprovalRecord
php artisan make:model SupplierQuote
php artisan make:model PurchaseOrder
php artisan make:model PurchaseOrderItem
php artisan make:model Payment
php artisan make:model Attachment
php artisan make:model BudgetAllocation
php artisan make:model AuditLog
```

---

## STEP 7 — Fill in each Model

Open each model file in `app/Models/` and replace the contents with the following.

---

### 7.1 — `User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class, 'requester_id');
    }

    public function approvalRecords()
    {
        return $this->hasMany(ApprovalRecord::class, 'actor_id');
    }
}
```

---

### 7.2 — `Department.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'code', 'hod_user_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function hod()
    {
        return $this->belongsTo(User::class, 'hod_user_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function costCentres()
    {
        return $this->hasMany(CostCentre::class);
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class);
    }
}
```

---

### 7.3 — `CostCentre.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCentre extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'name', 'department_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function purchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class);
    }

    public function budgetAllocations()
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function currentBudget(): ?BudgetAllocation
    {
        return $this->budgetAllocations()
            ->where('fiscal_year', now()->year)
            ->first();
    }
}
```

---

### 7.4 — `StockItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stock_code', 'description', 'category',
        'is_stocked', 'quantity_on_hand', 'reorder_level',
        'unit_of_measure', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_stocked'       => 'boolean',
            'is_active'        => 'boolean',
            'quantity_on_hand' => 'decimal:2',
            'reorder_level'    => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function purchaseRequisitionItems()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeStocked($query)
    {
        return $query->where('is_stocked', true)->where('is_active', true);
    }

    public function scopeOrderable($query)
    {
        return $query->where('is_stocked', false)->where('is_active', true);
    }
}
```

---

### 7.5 — `Supplier.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'contact_person', 'contact_email',
        'contact_phone', 'zppa_registration', 'zppa_approved', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'zppa_approved' => 'boolean',
            'is_active'     => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function quotes()
    {
        return $this->hasMany(SupplierQuote::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
```

---

### 7.6 — `PurchaseRequisition.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseRequisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_no', 'type', 'requester_id', 'department_id',
        'cost_centre_id', 'status', 'service_consumed_flag', 'notes',
    ];

    protected function casts(): array
    {
        return ['service_consumed_flag' => 'boolean'];
    }

    // ── Boot: auto-generate reference number ───────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (PurchaseRequisition $pr) {
            $pr->reference_no = 'PR-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        });
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function costCentre()
    {
        return $this->belongsTo(CostCentre::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function approvalRecords()
    {
        return $this->hasMany(ApprovalRecord::class)->orderBy('created_at');
    }

    public function supplierQuotes()
    {
        return $this->hasMany(SupplierQuote::class);
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    // ── Computed helpers ───────────────────────────────────────────────────

    public function totalEstimated(): float
    {
        return (float) $this->items->sum('total_price_estimated');
    }

    public function requiresThreeQuotes(): bool
    {
        // Threshold is configurable via config('procurement.quote_threshold')
        return $this->totalEstimated() >= config('procurement.quote_threshold', 10000);
    }

    public function latestApproval(string $stage): ?ApprovalRecord
    {
        return $this->approvalRecords->where('stage', $stage)->last();
    }
}
```

---

### 7.7 — `PurchaseRequisitionItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id', 'stock_item_id', 'description',
        'category', 'quantity', 'unit_of_measure',
        'unit_price_estimated', 'total_price_estimated',
    ];

    protected function casts(): array
    {
        return [
            'quantity'               => 'decimal:2',
            'unit_price_estimated'   => 'decimal:2',
            'total_price_estimated'  => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }
}
```

---

### 7.8 — `ApprovalRecord.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRecord extends Model
{
    use HasFactory;

    // Immutable — disable updated_at
    public $timestamps = false;

    protected $fillable = [
        'purchase_requisition_id', 'actor_id',
        'stage', 'decision', 'comment', 'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'acted_at'   => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeForStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }
}
```

---

### 7.9 — `SupplierQuote.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierQuote extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id', 'supplier_id', 'amount',
        'received_date', 'supplier_contacted',
        'contact_evidence_path', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'             => 'decimal:2',
            'received_date'      => 'date',
            'supplier_contacted' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function hasResponse(): bool
    {
        return ! is_null($this->amount);
    }
}
```

---

### 7.10 — `PurchaseOrder.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number', 'purchase_requisition_id', 'supplier_id',
        'total_value', 'status', 'issued_date',
        'expected_delivery_date', 'actual_delivery_date',
    ];

    protected function casts(): array
    {
        return [
            'total_value'            => 'decimal:2',
            'issued_date'            => 'date',
            'expected_delivery_date' => 'date',
            'actual_delivery_date'   => 'date',
        ];
    }

    // ── Boot ──────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $po) {
            $po->po_number = 'PO-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        });
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
```

---

### 7.11 — `PurchaseOrderItem.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'description', 'quantity',
        'unit_of_measure', 'unit_price', 'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity'    => 'decimal:2',
            'unit_price'  => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
```

---

### 7.12 — `Payment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'amount', 'status',
        'vc_approved_by', 'vc_approved_at',
        'bursar_confirmed_by', 'bursar_confirmed_at',
        'paid_at', 'delay_comment',
    ];

    protected function casts(): array
    {
        return [
            'amount'               => 'decimal:2',
            'vc_approved_at'       => 'datetime',
            'bursar_confirmed_at'  => 'datetime',
            'paid_at'              => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function vcApprover()
    {
        return $this->belongsTo(User::class, 'vc_approved_by');
    }

    public function bursarConfirmer()
    {
        return $this->belongsTo(User::class, 'bursar_confirmed_by');
    }
}
```

---

### 7.13 — `Attachment.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id', 'uploaded_by',
        'file_name', 'file_path', 'mime_type',
        'file_size', 'attachment_type',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
```

---

### 7.14 — `BudgetAllocation.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_centre_id', 'fiscal_year',
        'allocated_amount', 'committed_amount', 'spent_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount'  => 'decimal:2',
            'committed_amount'  => 'decimal:2',
            'spent_amount'      => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function costCentre()
    {
        return $this->belongsTo(CostCentre::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function availableBalance(): float
    {
        return (float) ($this->allocated_amount - $this->committed_amount - $this->spent_amount);
    }
}
```

---

### 7.15 — `AuditLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    // Immutable record — no updated_at
    public $timestamps = false;

    protected $fillable = [
        'entity_type', 'entity_id', 'action',
        'old_values', 'new_values',
        'actor_id', 'ip_address', 'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'logged_at'  => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    // ── Static helper ─────────────────────────────────────────────────────

    public static function record(
        string $entityType,
        int $entityId,
        string $action,
        array $oldValues = [],
        array $newValues = [],
        ?int $actorId = null,
        ?string $ip = null
    ): self {
        return static::create([
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

## STEP 8 — Add procurement config file

```bash
php artisan make:config procurement
```

If `make:config` is not available, manually create `config/procurement.php`:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quote Threshold
    |--------------------------------------------------------------------------
    | PRs with a total estimated value at or above this amount (ZMW) require
    | a minimum of 3 supplier quotes before a PO can be raised.
    */
    'quote_threshold' => env('PROCUREMENT_QUOTE_THRESHOLD', 10000),

    /*
    |--------------------------------------------------------------------------
    | Committee Evidence Threshold
    |--------------------------------------------------------------------------
    | If all received quotes exceed this value, evidence that the Procurement
    | Committee convened must be attached before proceeding.
    */
    'committee_threshold' => env('PROCUREMENT_COMMITTEE_THRESHOLD', 50000),

    /*
    |--------------------------------------------------------------------------
    | Minimum Quotes Required
    |--------------------------------------------------------------------------
    */
    'min_quotes_required' => 3,

];
```

---

## STEP 9 — Seed roles and permissions

```bash
php artisan make:seeder RolesAndPermissionsSeeder
```

Open `database/seeders/RolesAndPermissionsSeeder.php` and fill in:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Purchase Requisitions
            'pr.create', 'pr.view_own', 'pr.view_all',
            // Approval actions
            'pr.approve_hod', 'pr.approve_stores', 'pr.approve_bursar',
            'pr.approve_vc_requisition', 'pr.approve_vc_payment', 'pr.audit',
            // Procurement
            'pr.manage_quotes', 'pr.create_po',
            // Stock
            'stock.manage',
            // Suppliers
            'suppliers.manage',
            // Reports
            'reports.view',
            // Admin
            'admin.users', 'admin.roles',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        Role::firstOrCreate(['name' => 'requester'])->syncPermissions([
            'pr.create', 'pr.view_own',
        ]);

        Role::firstOrCreate(['name' => 'hod'])->syncPermissions([
            'pr.create', 'pr.view_own', 'pr.view_all', 'pr.approve_hod',
        ]);

        Role::firstOrCreate(['name' => 'stores'])->syncPermissions([
            'pr.view_all', 'pr.approve_stores', 'stock.manage',
        ]);

        Role::firstOrCreate(['name' => 'bursar'])->syncPermissions([
            'pr.view_all', 'pr.approve_bursar', 'reports.view',
        ]);

        Role::firstOrCreate(['name' => 'vc'])->syncPermissions([
            'pr.view_all', 'pr.approve_vc_requisition', 'pr.approve_vc_payment', 'reports.view',
        ]);

        Role::firstOrCreate(['name' => 'procurement'])->syncPermissions([
            'pr.view_all', 'pr.manage_quotes', 'pr.create_po', 'suppliers.manage',
        ]);

        Role::firstOrCreate(['name' => 'auditor'])->syncPermissions([
            'pr.view_all', 'pr.audit', 'reports.view',
        ]);

        Role::firstOrCreate(['name' => 'admin'])->syncPermissions(
            Permission::all()
        );
    }
}
```

Register the seeder in `DatabaseSeeder.php`:

```php
$this->call([
    RolesAndPermissionsSeeder::class,
]);
```

Run it:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## STEP 10 — Verify everything is wired up

```bash
php artisan migrate:status
php artisan tinker
```

In tinker, run these quick checks:

```php
// Check all tables exist
Schema::getTableListing();

// Check roles seeded
Spatie\Permission\Models\Role::pluck('name');

// Confirm PurchaseRequisition can be instantiated
new App\Models\PurchaseRequisition();

// Check AuditLog static helper works
App\Models\AuditLog::record('Test', 1, 'test', [], ['hello' => 'world'], null);
App\Models\AuditLog::first();
```

Exit tinker with `exit`.

---

## ✅ Phase 1 Complete

All 16 database tables, 14 Eloquent models, and role-based permissions are now in place.

**Next phase:** Controllers, Form Requests, Services (workflow engine), and Blade/Livewire views.

---

*Tables created: departments, cost_centres, users, model_has_permissions, model_has_roles, role_has_permissions, roles, permissions, stock_items, suppliers, purchase_requisitions, purchase_requisition_items, approval_records, supplier_quotes, purchase_orders, purchase_order_items, payments, attachments, budget_allocations, audit_logs*
