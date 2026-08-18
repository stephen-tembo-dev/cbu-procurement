<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', fn () => redirect()->route('dashboard'));
    Route::livewire('/dashboard', 'staff::dashboard')->name('dashboard');
    Route::livewire('/requisitions', 'staff::pr-list')->name('requisitions.index');
    Route::livewire('/requisitions/create', 'staff::pr-create')->name('requisitions.create');
    Route::livewire('/requisitions/{id}/edit', 'staff::pr-edit')->name('requisitions.edit');
    Route::livewire('/requisitions/{id}', 'staff::pr-show')->name('requisitions.show');
    Route::livewire('/purchase-orders',       'staff::po-list')->name('po.index');
    Route::livewire('/purchase-orders/{id}',  'staff::po-show')->name('po.show');
    Route::livewire('/requisitions/{id}/po/create', 'staff::po-create')->name('po.create');

    Route::livewire('/reports', 'staff::reports')->name('reports');
    Route::livewire('/budget', 'staff::budget-dashboard')->name('budget');
    Route::get('/reports/export/pdf',   [ReportExportController::class, 'pdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [ReportExportController::class, 'excel'])->name('reports.export.excel');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

    // Admin
    Route::livewire('/admin/users',            'staff::admin-users')->name('admin.users');
    Route::livewire('/admin/departments',       'staff::admin-departments')->name('admin.departments');
    Route::livewire('/admin/suppliers',         'staff::admin-suppliers')->name('admin.suppliers');
    Route::livewire('/admin/stock-items',       'staff::admin-stock-items')->name('admin.stock-items');
    Route::livewire('/admin/budget',            'staff::admin-budget')->name('admin.budget');
    Route::livewire('/admin/hod-delegations',   'staff::admin-hod-delegations')->name('admin.hod-delegations');
    Route::livewire('/admin/catalog',           'staff::admin-catalog')->name('admin.catalog');
});
