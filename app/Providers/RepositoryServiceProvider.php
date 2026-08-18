<?php

namespace App\Providers;

use App\Contracts\ApprovalRecordRepositoryInterface;
use App\Contracts\AttachmentRepositoryInterface;
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
    public array $bindings = [
        UserRepositoryInterface::class                   => UserRepository::class,
        DepartmentRepositoryInterface::class             => DepartmentRepository::class,
        CostCentreRepositoryInterface::class             => CostCentreRepository::class,
        StockItemRepositoryInterface::class              => StockItemRepository::class,
        SupplierRepositoryInterface::class               => SupplierRepository::class,
        PurchaseRequisitionRepositoryInterface::class     => PurchaseRequisitionRepository::class,
        PurchaseRequisitionItemRepositoryInterface::class => PurchaseRequisitionItemRepository::class,
        ApprovalRecordRepositoryInterface::class          => ApprovalRecordRepository::class,
        SupplierQuoteRepositoryInterface::class           => SupplierQuoteRepository::class,
        PurchaseOrderRepositoryInterface::class           => PurchaseOrderRepository::class,
        PaymentRepositoryInterface::class                 => PaymentRepository::class,
        AttachmentRepositoryInterface::class              => AttachmentRepository::class,
        BudgetAllocationRepositoryInterface::class        => BudgetAllocationRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
