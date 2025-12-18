<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\AccountReceivableController;
use App\Http\Controllers\AccountPayableController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankTransactionController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BankReconciliationController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AccountChartController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\GeneralLedgerController;
use App\Http\Controllers\AccountingSettingController;
use App\Http\Controllers\FinancialStatementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\RemissionController;
use App\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;

// Rutas publicas
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de autenticacion
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/login-history', [ProfileController::class, 'loginHistory'])->name('profile.login-history');

    // Usuarios
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    });
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:users.edit')->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->middleware('permission:users.edit')->name('users.toggle-status');

    // Roles
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/data', [RoleController::class, 'data'])->name('roles.data');
        Route::get('/roles/list', [RoleController::class, 'list'])->name('roles.list');
        Route::get('/roles/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.edit')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('roles.destroy');
    
    // Clientes
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/data', [CustomerController::class, 'data'])->name('customers.data');
    Route::get('/customers/list', [CustomerController::class, 'list'])->name('customers.list');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    
    // Proveedores
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/data', [SupplierController::class, 'data'])->name('suppliers.data');
    Route::get('/suppliers/list', [SupplierController::class, 'list'])->name('suppliers.list');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    
    // Categorías
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/data', [CategoryController::class, 'data'])->name('categories.data');
    Route::get('/categories/list', [CategoryController::class, 'list'])->name('categories.list');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // Productos
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/data', [ProductController::class, 'data'])->name('products.data');
    Route::get('/products/list', [ProductController::class, 'list'])->name('products.list');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // Ventas
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/data', [SaleController::class, 'data'])->name('sales.data');
    Route::get('/sales/list', [SaleController::class, 'list'])->name('sales.list');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/detail', [SaleController::class, 'detail'])->name('sales.detail');
    Route::get('/sales/{sale}/pdf', [SaleController::class, 'generatePDF'])->name('sales.pdf');
    Route::get('/sales/{sale}/download-pdf', [SaleController::class, 'downloadPDF'])->name('sales.download-pdf');
    Route::post('/sales/{sale}/confirm', [SaleController::class, 'confirm'])->name('sales.confirm');
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');

    // Notas de Crédito
    Route::get('/credit-notes', [CreditNoteController::class, 'index'])->name('credit-notes.index');
    Route::get('/credit-notes/data', [CreditNoteController::class, 'data'])->name('credit-notes.data');
    Route::get('/credit-notes/create', [CreditNoteController::class, 'create'])->name('credit-notes.create');
    Route::get('/credit-notes/sale-details/{sale}', [CreditNoteController::class, 'getSaleDetails'])->name('credit-notes.sale-details');
    Route::post('/credit-notes', [CreditNoteController::class, 'store'])->name('credit-notes.store');
    Route::get('/credit-notes/{creditNote}', [CreditNoteController::class, 'show'])->name('credit-notes.show');
    Route::get('/credit-notes/{creditNote}/pdf', [CreditNoteController::class, 'generatePDF'])->name('credit-notes.pdf');
    Route::get('/credit-notes/{creditNote}/download-pdf', [CreditNoteController::class, 'downloadPDF'])->name('credit-notes.download-pdf');
    Route::post('/credit-notes/{creditNote}/confirm', [CreditNoteController::class, 'confirm'])->name('credit-notes.confirm');
    Route::post('/credit-notes/{creditNote}/cancel', [CreditNoteController::class, 'cancel'])->name('credit-notes.cancel');

    // Remisiones
    Route::get('/remissions', [RemissionController::class, 'index'])->name('remissions.index');
    Route::get('/remissions/data', [RemissionController::class, 'data'])->name('remissions.data');
    Route::get('/remissions/create', [RemissionController::class, 'create'])->name('remissions.create');
    Route::post('/remissions', [RemissionController::class, 'store'])->name('remissions.store');
    Route::get('/remissions/{remission}', [RemissionController::class, 'show'])->name('remissions.show');
    Route::get('/remissions/{remission}/pdf', [RemissionController::class, 'generatePDF'])->name('remissions.pdf');
    Route::get('/remissions/{remission}/download-pdf', [RemissionController::class, 'downloadPDF'])->name('remissions.download-pdf');
    Route::post('/remissions/{remission}/confirm', [RemissionController::class, 'confirm'])->name('remissions.confirm');
    Route::post('/remissions/{remission}/deliver', [RemissionController::class, 'deliver'])->name('remissions.deliver');
    Route::post('/remissions/{remission}/convert-to-sale', [RemissionController::class, 'convertToSale'])->name('remissions.convert-to-sale');
    Route::post('/remissions/{remission}/cancel', [RemissionController::class, 'cancel'])->name('remissions.cancel');

    // Compras
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/data', [PurchaseController::class, 'data'])->name('purchases.data');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/purchases/{purchase}/detail', [PurchaseController::class, 'detail'])->name('purchases.detail');
    Route::post('/purchases/{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('purchases.confirm');
    Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    // Categorías de Gastos
    Route::get('/expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
    Route::get('/expense-categories/data', [ExpenseCategoryController::class, 'data'])->name('expense-categories.data');
    Route::get('/expense-categories/list', [ExpenseCategoryController::class, 'list'])->name('expense-categories.list');
    Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
    Route::get('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'show'])->name('expense-categories.show');
    Route::put('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
    Route::delete('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');

    // Gastos
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/data', [ExpenseController::class, 'data'])->name('expenses.data');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::post('/expenses/{expense}/pay', [ExpenseController::class, 'pay'])->name('expenses.pay');
    Route::post('/expenses/{expense}/cancel', [ExpenseController::class, 'cancel'])->name('expenses.cancel');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Reportes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
    Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/summary', [ReportController::class, 'summary'])->name('reports.summary');

    // Reportes Adicionales
    Route::get('/reports/cash-flow', [ReportController::class, 'cashFlow'])->name('reports.cash-flow');
    Route::get('/reports/aging-report', [ReportController::class, 'agingReport'])->name('reports.aging-report');
    Route::get('/reports/top-products', [ReportController::class, 'topProducts'])->name('reports.top-products');
    Route::get('/reports/profitability', [ReportController::class, 'profitability'])->name('reports.profitability');
    Route::get('/reports/inventory-movements', [ReportController::class, 'inventoryMovements'])->name('reports.inventory-movements');

    // Configuración de Empresa
    Route::get('/settings/company', [\App\Http\Controllers\CompanySettingController::class, 'index'])->name('settings.company');
    Route::put('/settings/company', [\App\Http\Controllers\CompanySettingController::class, 'update'])->name('settings.company.update');
    Route::delete('/settings/company/logo', [\App\Http\Controllers\CompanySettingController::class, 'deleteLogo'])->name('settings.company.delete-logo');

    // Configuración de Documentos
    Route::get('/settings/documents', [\App\Http\Controllers\DocumentSettingController::class, 'index'])->name('settings.documents');
    Route::post('/settings/documents', [\App\Http\Controllers\DocumentSettingController::class, 'store'])->name('settings.documents.store');
    Route::put('/settings/documents/{documentSetting}', [\App\Http\Controllers\DocumentSettingController::class, 'update'])->name('settings.documents.update');
    Route::delete('/settings/documents/{documentSetting}', [\App\Http\Controllers\DocumentSettingController::class, 'destroy'])->name('settings.documents.destroy');

    // Configuración de Impuestos
    Route::get('/settings/taxes', [\App\Http\Controllers\TaxSettingController::class, 'index'])->name('settings.taxes');
    Route::post('/settings/taxes', [\App\Http\Controllers\TaxSettingController::class, 'store'])->name('settings.taxes.store');
    Route::put('/settings/taxes/{taxSetting}', [\App\Http\Controllers\TaxSettingController::class, 'update'])->name('settings.taxes.update');
    Route::delete('/settings/taxes/{taxSetting}', [\App\Http\Controllers\TaxSettingController::class, 'destroy'])->name('settings.taxes.destroy');

    // Ajustes de Inventario
    Route::get('/inventory-adjustments', [InventoryAdjustmentController::class, 'index'])->name('inventory-adjustments.index');
    Route::get('/inventory-adjustments/data', [InventoryAdjustmentController::class, 'data'])->name('inventory-adjustments.data');
    Route::get('/inventory-adjustments/create', [InventoryAdjustmentController::class, 'create'])->name('inventory-adjustments.create');
    Route::post('/inventory-adjustments', [InventoryAdjustmentController::class, 'store'])->name('inventory-adjustments.store');
    Route::get('/inventory-adjustments/{inventoryAdjustment}', [InventoryAdjustmentController::class, 'show'])->name('inventory-adjustments.show');
    Route::post('/inventory-adjustments/{inventoryAdjustment}/confirm', [InventoryAdjustmentController::class, 'confirm'])->name('inventory-adjustments.confirm');
    Route::post('/inventory-adjustments/{inventoryAdjustment}/cancel', [InventoryAdjustmentController::class, 'cancel'])->name('inventory-adjustments.cancel');
    Route::delete('/inventory-adjustments/{inventoryAdjustment}', [InventoryAdjustmentController::class, 'destroy'])->name('inventory-adjustments.destroy');

    // Cuentas por Cobrar
    Route::get('/account-receivables', [AccountReceivableController::class, 'index'])->name('account-receivables.index');
    Route::get('/account-receivables/data', [AccountReceivableController::class, 'data'])->name('account-receivables.data');
    Route::get('/account-receivables/by-customer', [AccountReceivableController::class, 'byCustomer'])->name('account-receivables.by-customer');
    Route::get('/account-receivables/create', [AccountReceivableController::class, 'create'])->name('account-receivables.create');
    Route::post('/account-receivables', [AccountReceivableController::class, 'store'])->name('account-receivables.store');
    Route::get('/account-receivables/{accountReceivable}', [AccountReceivableController::class, 'show'])->name('account-receivables.show');
    Route::post('/account-receivables/{accountReceivable}/add-payment', [AccountReceivableController::class, 'addPayment'])->name('account-receivables.add-payment');
    Route::get('/account-receivables/payment/{payment}/pdf', [AccountReceivableController::class, 'generatePaymentPDF'])->name('account-receivables.payment-pdf');
    Route::get('/account-receivables/payment/{payment}/download-pdf', [AccountReceivableController::class, 'downloadPaymentPDF'])->name('account-receivables.download-payment-pdf');
    Route::post('/account-receivables/{accountReceivable}/cancel', [AccountReceivableController::class, 'cancel'])->name('account-receivables.cancel');
    Route::delete('/account-receivables/{accountReceivable}', [AccountReceivableController::class, 'destroy'])->name('account-receivables.destroy');

    // Cuentas por Pagar
    Route::get('/account-payables', [AccountPayableController::class, 'index'])->name('account-payables.index');
    Route::get('/account-payables/data', [AccountPayableController::class, 'data'])->name('account-payables.data');
    Route::get('/account-payables/by-supplier', [AccountPayableController::class, 'bySupplier'])->name('account-payables.by-supplier');
    Route::get('/account-payables/create', [AccountPayableController::class, 'create'])->name('account-payables.create');
    Route::post('/account-payables', [AccountPayableController::class, 'store'])->name('account-payables.store');
    Route::get('/account-payables/{accountPayable}', [AccountPayableController::class, 'show'])->name('account-payables.show');
    Route::post('/account-payables/{accountPayable}/add-payment', [AccountPayableController::class, 'addPayment'])->name('account-payables.add-payment');
    Route::get('/account-payables/payment/{payment}/pdf', [AccountPayableController::class, 'generatePaymentPDF'])->name('account-payables.payment-pdf');
    Route::get('/account-payables/payment/{payment}/download-pdf', [AccountPayableController::class, 'downloadPaymentPDF'])->name('account-payables.download-payment-pdf');
    Route::post('/account-payables/{accountPayable}/cancel', [AccountPayableController::class, 'cancel'])->name('account-payables.cancel');
    Route::delete('/account-payables/{accountPayable}', [AccountPayableController::class, 'destroy'])->name('account-payables.destroy');

    // Caja
    Route::get('/cash-registers', [CashRegisterController::class, 'index'])->name('cash-registers.index');
    Route::get('/cash-registers/data', [CashRegisterController::class, 'data'])->name('cash-registers.data');
    Route::get('/cash-registers/current', [CashRegisterController::class, 'current'])->name('cash-registers.current');
    Route::post('/cash-registers/open', [CashRegisterController::class, 'open'])->name('cash-registers.open');
    Route::get('/cash-registers/{cashRegister}', [CashRegisterController::class, 'show'])->name('cash-registers.show');
    Route::post('/cash-registers/{cashRegister}/add-movement', [CashRegisterController::class, 'addMovement'])->name('cash-registers.add-movement');
    Route::post('/cash-registers/{cashRegister}/close', [CashRegisterController::class, 'close'])->name('cash-registers.close');

    // Bancos - Catálogo de Bancos
    Route::get('/banks', [BankController::class, 'index'])->name('banks.index');
    Route::get('/banks/list', [BankController::class, 'list'])->name('banks.list');
    Route::get('/banks/active', [BankController::class, 'getActive'])->name('banks.active');
    Route::post('/banks', [BankController::class, 'store'])->name('banks.store');
    Route::get('/banks/{bank}', [BankController::class, 'show'])->name('banks.show');
    Route::put('/banks/{bank}', [BankController::class, 'update'])->name('banks.update');
    Route::delete('/banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');

    // Bancos - Cuentas Bancarias
    Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts.index');
    Route::get('/bank-accounts/data', [BankAccountController::class, 'data'])->name('bank-accounts.data');
    Route::get('/bank-accounts/list', [BankAccountController::class, 'list'])->name('bank-accounts.list');
    Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bank-accounts.store');
    Route::get('/bank-accounts/{bankAccount}', [BankAccountController::class, 'show'])->name('bank-accounts.show');
    Route::put('/bank-accounts/{bankAccount}', [BankAccountController::class, 'update'])->name('bank-accounts.update');
    Route::post('/bank-accounts/{bankAccount}/toggle-status', [BankAccountController::class, 'toggleStatus'])->name('bank-accounts.toggle-status');
    Route::post('/bank-accounts/{bankAccount}/set-default', [BankAccountController::class, 'setDefault'])->name('bank-accounts.set-default');
    Route::get('/bank-accounts/{bankAccount}/reconciliation', [BankAccountController::class, 'reconciliation'])->name('bank-accounts.reconciliation');
    Route::post('/bank-accounts/{bankAccount}/reconcile', [BankAccountController::class, 'reconcile'])->name('bank-accounts.reconcile');

    // Bancos - Transacciones
    Route::get('/bank-transactions', [BankTransactionController::class, 'index'])->name('bank-transactions.index');
    Route::get('/bank-transactions/data', [BankTransactionController::class, 'data'])->name('bank-transactions.data');
    Route::get('/bank-transactions/create', [BankTransactionController::class, 'create'])->name('bank-transactions.create');
    Route::post('/bank-transactions', [BankTransactionController::class, 'store'])->name('bank-transactions.store');
    Route::post('/bank-transactions/transfer', [BankTransactionController::class, 'transfer'])->name('bank-transactions.transfer');
    Route::post('/bank-transactions/cash-deposit', [BankTransactionController::class, 'cashDeposit'])->name('bank-transactions.cash-deposit');
    Route::post('/bank-transactions/cash-withdrawal', [BankTransactionController::class, 'cashWithdrawal'])->name('bank-transactions.cash-withdrawal');
    Route::get('/bank-transactions/{bankTransaction}', [BankTransactionController::class, 'show'])->name('bank-transactions.show');
    Route::post('/bank-transactions/{bankTransaction}/cancel', [BankTransactionController::class, 'cancel'])->name('bank-transactions.cancel');

    // Bancos - Cheques
    Route::get('/checks', [CheckController::class, 'index'])->name('checks.index');
    Route::get('/checks/data', [CheckController::class, 'data'])->name('checks.data');
    Route::get('/checks/create', [CheckController::class, 'create'])->name('checks.create');
    Route::post('/checks', [CheckController::class, 'store'])->name('checks.store');
    Route::get('/checks/{check}', [CheckController::class, 'show'])->name('checks.show');
    Route::post('/checks/{check}/deposit', [CheckController::class, 'depositCheck'])->name('checks.deposit');
    Route::post('/checks/{check}/cash', [CheckController::class, 'cashCheck'])->name('checks.cash');
    Route::post('/checks/{check}/bounce', [CheckController::class, 'bounceCheck'])->name('checks.bounce');
    Route::post('/checks/{check}/cancel', [CheckController::class, 'cancel'])->name('checks.cancel');

    // Bancos - Conciliación Bancaria
    Route::middleware('permission:bank-reconciliations.view')->group(function () {
        Route::get('/bank-reconciliations', [BankReconciliationController::class, 'index'])->name('bank-reconciliations.index');
        Route::get('/bank-reconciliations/data', [BankReconciliationController::class, 'data'])->name('bank-reconciliations.data');
        Route::get('/bank-reconciliations/{bankReconciliation}', [BankReconciliationController::class, 'show'])->name('bank-reconciliations.show');
        Route::get('/bank-reconciliations/{bankReconciliation}/report', [BankReconciliationController::class, 'report'])->name('bank-reconciliations.report');
        Route::get('/bank-reconciliations-unreconciled-transactions', [BankReconciliationController::class, 'getUnreconciledTransactions'])->name('bank-reconciliations.unreconciled-transactions');
    });
    Route::middleware('permission:bank-reconciliations.create')->group(function () {
        Route::get('/bank-reconciliations/create', [BankReconciliationController::class, 'create'])->name('bank-reconciliations.create');
        Route::post('/bank-reconciliations', [BankReconciliationController::class, 'store'])->name('bank-reconciliations.store');
    });
    Route::middleware('permission:bank-reconciliations.edit')->group(function () {
        Route::get('/bank-reconciliations/{bankReconciliation}/edit', [BankReconciliationController::class, 'edit'])->name('bank-reconciliations.edit');
        Route::put('/bank-reconciliations/{bankReconciliation}', [BankReconciliationController::class, 'update'])->name('bank-reconciliations.update');
    });
    Route::post('/bank-reconciliations/{bankReconciliation}/post', [BankReconciliationController::class, 'post'])
        ->middleware('permission:bank-reconciliations.post')
        ->name('bank-reconciliations.post');
    Route::post('/bank-reconciliations/{bankReconciliation}/cancel', [BankReconciliationController::class, 'cancel'])
        ->middleware('permission:bank-reconciliations.cancel')
        ->name('bank-reconciliations.cancel');
    Route::delete('/bank-reconciliations/{bankReconciliation}', [BankReconciliationController::class, 'destroy'])
        ->middleware('permission:bank-reconciliations.delete')
        ->name('bank-reconciliations.destroy');

    // Contabilidad - Plan de Cuentas
    Route::get('/account-chart', [AccountChartController::class, 'index'])->name('account-chart.index');
    Route::get('/account-chart/tree', [AccountChartController::class, 'tree'])->name('account-chart.tree');
    Route::get('/account-chart/detail-accounts', [AccountChartController::class, 'detailAccounts'])->name('account-chart.detail-accounts');
    Route::get('/account-chart/generate-code', [AccountChartController::class, 'generateCode'])->name('account-chart.generate-code');
    Route::post('/account-chart', [AccountChartController::class, 'store'])->name('account-chart.store');
    Route::get('/account-chart/{account}', [AccountChartController::class, 'show'])->name('account-chart.show');
    Route::put('/account-chart/{account}', [AccountChartController::class, 'update'])->name('account-chart.update');
    Route::delete('/account-chart/{account}', [AccountChartController::class, 'destroy'])->name('account-chart.destroy');

    // Contabilidad - Asientos Contables
    Route::get('/journal-entries', [JournalEntryController::class, 'index'])->name('journal-entries.index');
    Route::get('/journal-entries/data', [JournalEntryController::class, 'data'])->name('journal-entries.data');
    Route::get('/journal-entries/create', [JournalEntryController::class, 'create'])->name('journal-entries.create');
    Route::post('/journal-entries', [JournalEntryController::class, 'store'])->name('journal-entries.store');
    Route::get('/journal-entries/{entry}', [JournalEntryController::class, 'show'])->name('journal-entries.show');
    Route::get('/journal-entries/{entry}/edit', [JournalEntryController::class, 'edit'])->name('journal-entries.edit');
    Route::put('/journal-entries/{entry}', [JournalEntryController::class, 'update'])->name('journal-entries.update');
    Route::delete('/journal-entries/{entry}', [JournalEntryController::class, 'destroy'])->name('journal-entries.destroy');
    Route::post('/journal-entries/{entry}/post', [JournalEntryController::class, 'post'])->name('journal-entries.post');
    Route::post('/journal-entries/{entry}/cancel', [JournalEntryController::class, 'cancel'])->name('journal-entries.cancel');

    // Contabilidad - Reportes
    Route::get('/general-ledger', [GeneralLedgerController::class, 'index'])->name('general-ledger.index');
    Route::get('/general-ledger/data', [GeneralLedgerController::class, 'data'])->name('general-ledger.data');
    Route::get('/general-ledger/export', [GeneralLedgerController::class, 'exportGeneralLedger'])->name('general-ledger.export');
    Route::get('/trial-balance', [GeneralLedgerController::class, 'trialBalance'])->name('trial-balance.index');
    Route::get('/trial-balance/data', [GeneralLedgerController::class, 'trialBalanceData'])->name('trial-balance.data');
    Route::get('/trial-balance/export', [GeneralLedgerController::class, 'exportTrialBalance'])->name('trial-balance.export');

    // Contabilidad - Configuración
    Route::get('/accounting-settings', [AccountingSettingController::class, 'index'])->name('accounting-settings.index');
    Route::post('/accounting-settings', [AccountingSettingController::class, 'update'])->name('accounting-settings.update');

    // Estados Financieros
    Route::get('/accounting/balance-sheet', [FinancialStatementController::class, 'balanceSheet'])->name('accounting.balance-sheet');
    Route::get('/accounting/income-statement', [FinancialStatementController::class, 'incomeStatement'])->name('accounting.income-statement');

    // Centro de Ayuda
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
    Route::get('/help/contextual', [HelpController::class, 'contextual'])->name('help.contextual');
    Route::get('/help/search', [HelpController::class, 'search'])->name('help.search');
    Route::get('/help/{slug}', [HelpController::class, 'show'])->name('help.show');
});
