<?php

namespace App\Services;

use App\Models\AccountingSetting;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\AccountReceivablePayment;
use App\Models\AccountPayablePayment;
use App\Models\CreditNote;
use Illuminate\Support\Facades\DB;

class AccountingIntegrationService
{
    /**
     * Crear asiento contable para una venta
     */
    public function createSaleJournalEntry(Sale $sale): JournalEntry
    {
        $tenantId = $sale->tenant_id;

        // Verificar que las cuentas necesarias estén configuradas
        $this->validateSaleAccounts($tenantId, $sale);

        // Determinar la cuenta de débito según el método de pago
        $debitAccountId = $this->getDebitAccountForSale($tenantId, $sale);

        // Obtener cuenta de ingresos
        $creditAccountId = AccountingSetting::getValue($tenantId, 'sales_income');

        // Obtener cuenta de IVA ventas si hay IVA
        $taxAccountId = null;
        if ($sale->total_iva > 0) {
            $taxAccountId = AccountingSetting::getValue($tenantId, 'sales_tax');
        }

        // Crear el asiento contable
        DB::beginTransaction();
        try {
            $period = date('Y-m', strtotime($sale->sale_date));

            $journalEntry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'entry_number' => JournalEntry::generateEntryNumber($tenantId, $period),
                'entry_date' => $sale->sale_date,
                'period' => $period,
                'reference' => $sale->sale_number,
                'description' => $this->getSaleDescription($sale),
                'status' => 'posted',
                'user_id' => $sale->user_id,
            ]);

            // Calcular subtotal sin IVA
            $subtotalWithoutTax = $sale->subtotal_exento + $sale->subtotal_5 + $sale->subtotal_10;

            // Línea de débito: Caja/Banco/Cuentas por Cobrar
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $debitAccountId,
                'description' => $this->getSaleDescription($sale),
                'debit' => $sale->total,
                'credit' => 0,
            ]);

            // Línea de crédito: Ingresos por Ventas (sin IVA)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $creditAccountId,
                'description' => 'Venta ' . $sale->sale_number,
                'debit' => 0,
                'credit' => $subtotalWithoutTax,
            ]);

            // Si hay IVA, agregar línea de crédito para IVA
            if ($sale->total_iva > 0 && $taxAccountId) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxAccountId,
                    'description' => 'IVA Ventas ' . $sale->sale_number,
                    'debit' => 0,
                    'credit' => $sale->total_iva,
                ]);
            }

            // Actualizar saldos de cuentas
            $journalEntry->updateAccountBalances();

            // Vincular el asiento a la venta
            $sale->journal_entry_id = $journalEntry->id;
            $sale->save();

            DB::commit();

            return $journalEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reversar asiento contable de una venta anulada
     */
    public function reverseSaleJournalEntry(Sale $sale): ?JournalEntry
    {
        if (!$sale->journal_entry_id) {
            return null;
        }

        $originalEntry = JournalEntry::find($sale->journal_entry_id);
        if (!$originalEntry) {
            return null;
        }

        DB::beginTransaction();
        try {
            // Crear asiento de reversa
            $period = date('Y-m');

            $reversalEntry = JournalEntry::create([
                'tenant_id' => $sale->tenant_id,
                'entry_number' => JournalEntry::generateEntryNumber($sale->tenant_id, $period),
                'entry_date' => now()->toDateString(),
                'period' => $period,
                'reference' => $sale->sale_number . ' (Anulación)',
                'description' => 'Anulación de venta ' . $sale->sale_number,
                'status' => 'posted',
                'user_id' => auth()->id(),
            ]);

            // Crear líneas inversas (intercambiar débito y crédito)
            foreach ($originalEntry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'account_id' => $line->account_id,
                    'description' => 'Reversa: ' . $line->description,
                    'debit' => $line->credit,  // Invertir
                    'credit' => $line->debit,  // Invertir
                ]);
            }

            // Actualizar saldos de cuentas
            $reversalEntry->updateAccountBalances();

            DB::commit();

            return $reversalEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validar que las cuentas necesarias estén configuradas
     */
    private function validateSaleAccounts(int $tenantId, Sale $sale): void
    {
        $errors = [];

        // Validar cuenta de ingresos
        if (!AccountingSetting::getValue($tenantId, 'sales_income')) {
            $errors[] = 'Cuenta de Ingresos por Ventas';
        }

        // Validar cuenta según método de pago
        if ($sale->payment_type === 'credit') {
            if (!AccountingSetting::getValue($tenantId, 'accounts_receivable')) {
                $errors[] = 'Cuenta de Cuentas por Cobrar';
            }
        } else {
            // Venta al contado
            if ($sale->payment_method === 'cash') {
                if (!AccountingSetting::getValue($tenantId, 'cash')) {
                    $errors[] = 'Cuenta de Caja';
                }
            } elseif (in_array($sale->payment_method, ['card', 'transfer'])) {
                if (!AccountingSetting::getValue($tenantId, 'bank_default')) {
                    $errors[] = 'Cuenta de Banco por Defecto';
                }
            }
        }

        // Validar cuenta de IVA si hay IVA
        if ($sale->total_iva > 0) {
            if (!AccountingSetting::getValue($tenantId, 'sales_tax')) {
                $errors[] = 'Cuenta de IVA Ventas';
            }
        }

        if (!empty($errors)) {
            throw new \Exception(
                'Debe configurar las siguientes cuentas contables antes de confirmar la venta: ' .
                implode(', ', $errors) . '. ' .
                'Vaya a Contabilidad > Configuración Contable para configurarlas.'
            );
        }
    }

    /**
     * Obtener la cuenta de débito según el tipo y método de pago
     */
    private function getDebitAccountForSale(int $tenantId, Sale $sale): int
    {
        // Si es venta a crédito, usar cuentas por cobrar
        if ($sale->payment_type === 'credit') {
            return AccountingSetting::getValue($tenantId, 'accounts_receivable');
        }

        // Si es venta al contado, determinar según método de pago
        if ($sale->payment_method === 'Efectivo') {
            return AccountingSetting::getValue($tenantId, 'Efectivo');
        } elseif (in_array($sale->payment_method, ['Tarjeta', 'Transferencia'])) {
            return AccountingSetting::getValue($tenantId, 'bank_default');
        }

        // Por defecto, usar caja
        return AccountingSetting::getValue($tenantId, 'Efectivo');
    }

    /**
     * Obtener descripción del asiento de venta
     */
    private function getSaleDescription(Sale $sale): string
    {
        $description = 'Venta ' . $sale->sale_number;

        if ($sale->customer) {
            $description .= ' - ' . $sale->customer->name;
        }

        return $description;
    }

    /**
     * Crear asiento contable para una compra
     */
    public function createPurchaseJournalEntry(Purchase $purchase): JournalEntry
    {
        $tenantId = $purchase->tenant_id;

        // Verificar que las cuentas necesarias estén configuradas
        $this->validatePurchaseAccounts($tenantId, $purchase);

        // Determinar la cuenta de crédito según el método de pago
        $creditAccountId = $this->getCreditAccountForPurchase($tenantId, $purchase);

        // Obtener cuenta de gastos/costo de ventas
        $debitAccountId = AccountingSetting::getValue($tenantId, 'purchases_expense');

        // Obtener cuenta de IVA compras si hay IVA
        $taxAccountId = null;
        if ($purchase->total_iva > 0) {
            $taxAccountId = AccountingSetting::getValue($tenantId, 'purchases_tax');
        }

        // Crear el asiento contable
        DB::beginTransaction();
        try {
            $period = date('Y-m', strtotime($purchase->purchase_date));

            $journalEntry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'entry_number' => JournalEntry::generateEntryNumber($tenantId, $period),
                'entry_date' => $purchase->purchase_date,
                'period' => $period,
                'reference' => $purchase->purchase_number,
                'description' => $this->getPurchaseDescription($purchase),
                'status' => 'posted',
                'user_id' => $purchase->user_id,
            ]);

            // Calcular subtotal sin IVA
            $subtotalWithoutTax = $purchase->subtotal_exento + $purchase->subtotal_5 + $purchase->subtotal_10;

            // Línea de débito: Costo de Ventas / Compras (sin IVA)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $debitAccountId,
                'description' => 'Compra ' . $purchase->purchase_number,
                'debit' => $subtotalWithoutTax,
                'credit' => 0,
            ]);

            // Si hay IVA, agregar línea de débito para IVA Crédito Fiscal
            if ($purchase->total_iva > 0 && $taxAccountId) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxAccountId,
                    'description' => 'IVA Compras ' . $purchase->purchase_number,
                    'debit' => $purchase->total_iva,
                    'credit' => 0,
                ]);
            }

            // Línea de crédito: Caja/Banco/Cuentas por Pagar
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $creditAccountId,
                'description' => $this->getPurchaseDescription($purchase),
                'debit' => 0,
                'credit' => $purchase->total,
            ]);

            // Actualizar saldos de cuentas
            $journalEntry->updateAccountBalances();

            // Vincular el asiento a la compra
            $purchase->journal_entry_id = $journalEntry->id;
            $purchase->save();

            DB::commit();

            return $journalEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reversar asiento contable de una compra anulada
     */
    public function reversePurchaseJournalEntry(Purchase $purchase): ?JournalEntry
    {
        if (!$purchase->journal_entry_id) {
            return null;
        }

        $originalEntry = JournalEntry::find($purchase->journal_entry_id);
        if (!$originalEntry) {
            return null;
        }

        DB::beginTransaction();
        try {
            // Crear asiento de reversa
            $period = date('Y-m');

            $reversalEntry = JournalEntry::create([
                'tenant_id' => $purchase->tenant_id,
                'entry_number' => JournalEntry::generateEntryNumber($purchase->tenant_id, $period),
                'entry_date' => now()->toDateString(),
                'period' => $period,
                'reference' => $purchase->purchase_number . ' (Anulación)',
                'description' => 'Anulación de compra ' . $purchase->purchase_number,
                'status' => 'posted',
                'user_id' => auth()->id(),
            ]);

            // Crear líneas inversas (intercambiar débito y crédito)
            foreach ($originalEntry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'account_id' => $line->account_id,
                    'description' => 'Reversa: ' . $line->description,
                    'debit' => $line->credit,  // Invertir
                    'credit' => $line->debit,  // Invertir
                ]);
            }

            // Actualizar saldos de cuentas
            $reversalEntry->updateAccountBalances();

            DB::commit();

            return $reversalEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validar que las cuentas necesarias para compras estén configuradas
     */
    private function validatePurchaseAccounts(int $tenantId, Purchase $purchase): void
    {
        $errors = [];

        // Validar cuenta de gastos/costo de ventas
        if (!AccountingSetting::getValue($tenantId, 'purchases_expense')) {
            $errors[] = 'Cuenta de Compras / Costo de Ventas';
        }

        // Validar cuenta según método de pago
        if ($purchase->payment_type === 'credit') {
            if (!AccountingSetting::getValue($tenantId, 'accounts_payable')) {
                $errors[] = 'Cuenta de Cuentas por Pagar';
            }
        } else {
            // Compra al contado
            if ($purchase->payment_method === 'cash') {
                if (!AccountingSetting::getValue($tenantId, 'cash')) {
                    $errors[] = 'Cuenta de Caja';
                }
            } elseif (in_array($purchase->payment_method, ['card', 'transfer'])) {
                if (!AccountingSetting::getValue($tenantId, 'bank_default')) {
                    $errors[] = 'Cuenta de Banco por Defecto';
                }
            }
        }

        // Validar cuenta de IVA si hay IVA
        if ($purchase->total_iva > 0) {
            if (!AccountingSetting::getValue($tenantId, 'purchases_tax')) {
                $errors[] = 'Cuenta de IVA Compras';
            }
        }

        if (!empty($errors)) {
            throw new \Exception(
                'Debe configurar las siguientes cuentas contables antes de confirmar la compra: ' .
                implode(', ', $errors) . '. ' .
                'Vaya a Contabilidad > Configuración Contable para configurarlas.'
            );
        }
    }

    /**
     * Obtener la cuenta de crédito según el tipo y método de pago
     */
    private function getCreditAccountForPurchase(int $tenantId, Purchase $purchase): int
    {
        // Si es compra a crédito, usar cuentas por pagar
        if ($purchase->payment_type === 'credit') {
            return AccountingSetting::getValue($tenantId, 'accounts_payable');
        }

        // Si es compra al contado, determinar según método de pago
        if ($purchase->payment_method === 'cash') {
            return AccountingSetting::getValue($tenantId, 'cash');
        } elseif (in_array($purchase->payment_method, ['card', 'transfer'])) {
            return AccountingSetting::getValue($tenantId, 'bank_default');
        }

        // Por defecto, usar caja
        return AccountingSetting::getValue($tenantId, 'cash');
    }

    /**
     * Obtener descripción del asiento de compra
     */
    private function getPurchaseDescription(Purchase $purchase): string
    {
        $description = 'Compra ' . $purchase->purchase_number;

        if ($purchase->supplier) {
            $description .= ' - ' . $purchase->supplier->name;
        }

        return $description;
    }

    /**
     * Crear asiento contable para un pago de cuenta por cobrar
     */
    public function createReceivablePaymentJournalEntry(AccountReceivablePayment $payment, int $tenantId): JournalEntry
    {
        // Validar que las cuentas necesarias estén configuradas
        $this->validateReceivablePaymentAccounts($tenantId, $payment);

        // Determinar cuenta de débito según método de pago
        $debitAccountId = $this->getDebitAccountForPayment($tenantId, $payment->payment_method);

        // Cuenta de crédito: Cuentas por Cobrar
        $creditAccountId = AccountingSetting::getValue($tenantId, 'accounts_receivable');

        DB::beginTransaction();
        try {
            $period = date('Y-m', strtotime($payment->payment_date));

            $journalEntry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'entry_number' => JournalEntry::generateEntryNumber($tenantId, $period),
                'entry_date' => $payment->payment_date,
                'period' => $period,
                'reference' => $payment->payment_number,
                'description' => 'Cobro ' . $payment->payment_number . ' - ' . $payment->accountReceivable->customer_name,
                'status' => 'posted',
                'user_id' => $payment->user_id,
            ]);

            // DÉBITO: Caja/Banco (entrada de dinero)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $debitAccountId,
                'description' => 'Cobro ' . $payment->payment_number,
                'debit' => $payment->amount,
                'credit' => 0,
            ]);

            // CRÉDITO: Cuentas por Cobrar (disminuye el activo)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $creditAccountId,
                'description' => 'Cobro ' . $payment->payment_number . ' - ' . $payment->accountReceivable->document_number,
                'debit' => 0,
                'credit' => $payment->amount,
            ]);

            // Actualizar saldos de cuentas
            $journalEntry->updateAccountBalances();

            // Vincular el asiento al pago
            $payment->journal_entry_id = $journalEntry->id;
            $payment->save();

            DB::commit();

            return $journalEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Crear asiento contable para un pago de cuenta por pagar
     */
    public function createPayablePaymentJournalEntry(AccountPayablePayment $payment, int $tenantId): JournalEntry
    {
        // Validar que las cuentas necesarias estén configuradas
        $this->validatePayablePaymentAccounts($tenantId, $payment);

        // Determinar cuenta de crédito según método de pago
        $creditAccountId = $this->getCreditAccountForPayment($tenantId, $payment->payment_method);

        // Cuenta de débito: Cuentas por Pagar
        $debitAccountId = AccountingSetting::getValue($tenantId, 'accounts_payable');

        DB::beginTransaction();
        try {
            $period = date('Y-m', strtotime($payment->payment_date));

            $journalEntry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'entry_number' => JournalEntry::generateEntryNumber($tenantId, $period),
                'entry_date' => $payment->payment_date,
                'period' => $period,
                'reference' => $payment->payment_number,
                'description' => 'Pago ' . $payment->payment_number . ' - ' . $payment->accountPayable->supplier_name,
                'status' => 'posted',
                'user_id' => $payment->user_id,
            ]);

            // DÉBITO: Cuentas por Pagar (disminuye el pasivo)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $debitAccountId,
                'description' => 'Pago ' . $payment->payment_number . ' - ' . $payment->accountPayable->document_number,
                'debit' => $payment->amount,
                'credit' => 0,
            ]);

            // CRÉDITO: Caja/Banco (salida de dinero)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $creditAccountId,
                'description' => 'Pago ' . $payment->payment_number,
                'debit' => 0,
                'credit' => $payment->amount,
            ]);

            // Actualizar saldos de cuentas
            $journalEntry->updateAccountBalances();

            // Vincular el asiento al pago
            $payment->journal_entry_id = $journalEntry->id;
            $payment->save();

            DB::commit();

            return $journalEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validar cuentas para pagos de cuentas por cobrar
     */
    private function validateReceivablePaymentAccounts(int $tenantId, AccountReceivablePayment $payment): void
    {
        $errors = [];

        if (!AccountingSetting::getValue($tenantId, 'accounts_receivable')) {
            $errors[] = 'Cuenta de Cuentas por Cobrar';
        }

        if ($payment->payment_method === 'cash') {
            if (!AccountingSetting::getValue($tenantId, 'cash')) {
                $errors[] = 'Cuenta de Caja';
            }
        } elseif (in_array($payment->payment_method, ['card', 'transfer'])) {
            if (!AccountingSetting::getValue($tenantId, 'bank_default')) {
                $errors[] = 'Cuenta de Banco por Defecto';
            }
        }

        if (!empty($errors)) {
            throw new \Exception(
                'Debe configurar las siguientes cuentas contables antes de registrar el cobro: ' .
                implode(', ', $errors) . '. ' .
                'Vaya a Contabilidad > Configuración Contable para configurarlas.'
            );
        }
    }

    /**
     * Validar cuentas para pagos de cuentas por pagar
     */
    private function validatePayablePaymentAccounts(int $tenantId, AccountPayablePayment $payment): void
    {
        $errors = [];

        if (!AccountingSetting::getValue($tenantId, 'accounts_payable')) {
            $errors[] = 'Cuenta de Cuentas por Pagar';
        }

        if ($payment->payment_method === 'cash') {
            if (!AccountingSetting::getValue($tenantId, 'cash')) {
                $errors[] = 'Cuenta de Caja';
            }
        } elseif (in_array($payment->payment_method, ['card', 'transfer'])) {
            if (!AccountingSetting::getValue($tenantId, 'bank_default')) {
                $errors[] = 'Cuenta de Banco por Defecto';
            }
        }

        if (!empty($errors)) {
            throw new \Exception(
                'Debe configurar las siguientes cuentas contables antes de registrar el pago: ' .
                implode(', ', $errors) . '. ' .
                'Vaya a Contabilidad > Configuración Contable para configurarlas.'
            );
        }
    }

    /**
     * Obtener cuenta de débito para pagos recibidos
     */
    private function getDebitAccountForPayment(int $tenantId, string $paymentMethod): int
    {
        if ($paymentMethod === 'cash') {
            return AccountingSetting::getValue($tenantId, 'cash');
        } elseif (in_array($paymentMethod, ['card', 'transfer'])) {
            return AccountingSetting::getValue($tenantId, 'bank_default');
        }

        // Por defecto, caja
        return AccountingSetting::getValue($tenantId, 'cash');
    }

    /**
     * Obtener cuenta de crédito para pagos realizados
     */
    private function getCreditAccountForPayment(int $tenantId, string $paymentMethod): int
    {
        if ($paymentMethod === 'cash') {
            return AccountingSetting::getValue($tenantId, 'cash');
        } elseif (in_array($paymentMethod, ['card', 'transfer'])) {
            return AccountingSetting::getValue($tenantId, 'bank_default');
        }

        // Por defecto, caja
        return AccountingSetting::getValue($tenantId, 'cash');
    }

    /**
     * Crear asiento contable para un gasto
     */
    public function createExpenseJournalEntry(\App\Models\Expense $expense): JournalEntry
    {
        $tenantId = $expense->tenant_id;

        // Validar que las cuentas necesarias estén configuradas
        $this->validateExpenseAccounts($tenantId, $expense);

        // Determinar cuenta de crédito según método de pago
        $creditAccountId = $this->getCreditAccountForExpense($tenantId, $expense);

        // Obtener cuenta de gastos: primero intentar de la categoría, luego usar cuenta por defecto
        $debitAccountId = null;
        if ($expense->category && $expense->category->account_id) {
            // Usar cuenta de la categoría si está configurada
            $debitAccountId = $expense->category->account_id;
        } else {
            // Usar cuenta de gastos por defecto como fallback
            $debitAccountId = AccountingSetting::getValue($tenantId, 'expenses_default');
        }

        // Si no hay ninguna cuenta disponible, lanzar error (esto no debería pasar por validateExpenseAccounts)
        if (!$debitAccountId) {
            throw new \Exception('No se pudo determinar la cuenta de gasto. Configure la cuenta en la categoría o en Configuración Contable.');
        }

        // Obtener cuenta de IVA compras si hay IVA
        $taxAccountId = null;
        if ($expense->tax_amount > 0) {
            $taxAccountId = AccountingSetting::getValue($tenantId, 'purchases_tax');
        }

        // Crear el asiento contable
        DB::beginTransaction();
        try {
            $period = date('Y-m', strtotime($expense->expense_date));

            $journalEntry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'entry_number' => JournalEntry::generateEntryNumber($tenantId, $period),
                'entry_date' => $expense->expense_date,
                'period' => $period,
                'reference' => $expense->expense_number,
                'description' => $this->getExpenseDescription($expense),
                'status' => 'posted',
                'user_id' => $expense->user_id,
            ]);

            // Calcular monto sin IVA
            $amountWithoutTax = $expense->amount - $expense->tax_amount;

            // Línea de débito: Gasto (cuenta de la categoría)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $debitAccountId,
                'description' => $expense->description,
                'debit' => $amountWithoutTax,
                'credit' => 0,
            ]);

            // Si hay IVA, agregar línea de débito para IVA Crédito Fiscal
            if ($expense->tax_amount > 0 && $taxAccountId) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxAccountId,
                    'description' => 'IVA ' . $expense->tax_rate . '% - ' . $expense->expense_number,
                    'debit' => $expense->tax_amount,
                    'credit' => 0,
                ]);
            }

            // Línea de crédito: Caja/Banco
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $creditAccountId,
                'description' => $this->getExpenseDescription($expense),
                'debit' => 0,
                'credit' => $expense->amount,
            ]);

            // Actualizar saldos de cuentas
            $journalEntry->updateAccountBalances();

            // Vincular el asiento al gasto
            $expense->journal_entry_id = $journalEntry->id;
            $expense->save();

            DB::commit();

            return $journalEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reversar asiento contable de un gasto anulado
     */
    public function reverseExpenseJournalEntry(\App\Models\Expense $expense): ?JournalEntry
    {
        if (!$expense->journal_entry_id) {
            return null;
        }

        $originalEntry = JournalEntry::find($expense->journal_entry_id);
        if (!$originalEntry) {
            return null;
        }

        DB::beginTransaction();
        try {
            // Crear asiento de reversa
            $period = date('Y-m');

            $reversalEntry = JournalEntry::create([
                'tenant_id' => $expense->tenant_id,
                'entry_number' => JournalEntry::generateEntryNumber($expense->tenant_id, $period),
                'entry_date' => now()->toDateString(),
                'period' => $period,
                'reference' => $expense->expense_number . ' (Anulación)',
                'description' => 'Anulación de gasto ' . $expense->expense_number,
                'status' => 'posted',
                'user_id' => auth()->id(),
            ]);

            // Crear líneas inversas (intercambiar débito y crédito)
            foreach ($originalEntry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversalEntry->id,
                    'account_id' => $line->account_id,
                    'description' => 'Reversa: ' . $line->description,
                    'debit' => $line->credit,  // Invertir
                    'credit' => $line->debit,  // Invertir
                ]);
            }

            // Actualizar saldos de cuentas
            $reversalEntry->updateAccountBalances();

            DB::commit();

            return $reversalEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validar que las cuentas necesarias para gastos estén configuradas
     */
    private function validateExpenseAccounts(int $tenantId, \App\Models\Expense $expense): void
    {
        $errors = [];

        // Validar que exista al menos una cuenta de gasto: de la categoría o la cuenta por defecto
        $hasExpenseAccount = false;
        if ($expense->category && $expense->category->account_id) {
            // La categoría tiene cuenta asignada
            $hasExpenseAccount = true;
        } elseif (AccountingSetting::getValue($tenantId, 'expenses_default')) {
            // Usar cuenta de gastos por defecto como fallback
            $hasExpenseAccount = true;
        }

        if (!$hasExpenseAccount) {
            $errors[] = 'Cuenta de Gasto (asignada a la categoría o Cuenta de Gastos por Defecto en Configuración Contable)';
        }

        // Validar cuenta según método de pago
        if ($expense->payment_method === 'cash') {
            if (!AccountingSetting::getValue($tenantId, 'cash')) {
                $errors[] = 'Cuenta de Caja';
            }
        } elseif (in_array($expense->payment_method, ['card', 'transfer', 'debit'])) {
            if (!AccountingSetting::getValue($tenantId, 'bank_default')) {
                $errors[] = 'Cuenta de Banco por Defecto';
            }
        }

        // Validar cuenta de IVA si hay IVA
        if ($expense->tax_amount > 0) {
            if (!AccountingSetting::getValue($tenantId, 'purchases_tax')) {
                $errors[] = 'Cuenta de IVA Compras (Crédito Fiscal)';
            }
        }

        if (!empty($errors)) {
            throw new \Exception(
                'Debe configurar las siguientes cuentas contables antes de pagar el gasto: ' .
                implode(', ', $errors) . '. ' .
                'Vaya a Contabilidad > Configuración Contable para configurarlas.'
            );
        }
    }

    /**
     * Obtener la cuenta de crédito según el método de pago del gasto
     */
    private function getCreditAccountForExpense(int $tenantId, \App\Models\Expense $expense): int
    {
        if ($expense->payment_method === 'cash') {
            return AccountingSetting::getValue($tenantId, 'cash');
        } elseif (in_array($expense->payment_method, ['card', 'transfer', 'debit'])) {
            return AccountingSetting::getValue($tenantId, 'bank_default');
        }

        // Por defecto, usar caja
        return AccountingSetting::getValue($tenantId, 'cash');
    }

    /**
     * Obtener descripción del asiento de gasto
     */
    private function getExpenseDescription(\App\Models\Expense $expense): string
    {
        $description = 'Gasto ' . $expense->expense_number;

        if ($expense->supplier) {
            $description .= ' - ' . $expense->supplier->name;
        }

        if ($expense->category) {
            $description .= ' (' . $expense->category->name . ')';
        }

        return $description;
    }

    /**
     * Crear asiento contable para una nota de crédito (reversión de venta)
     */
    public function createCreditNoteJournalEntry(CreditNote $creditNote): JournalEntry
    {
        $tenantId = $creditNote->tenant_id;
        $sale = $creditNote->sale;

        // Verificar que las cuentas necesarias estén configuradas
        $this->validateCreditNoteAccounts($tenantId, $sale);

        // Determinar la cuenta de crédito según el método de pago original de la venta
        $creditAccountId = $this->getDebitAccountForSale($tenantId, $sale);

        // Obtener cuenta de ingresos
        $debitAccountId = AccountingSetting::getValue($tenantId, 'sales_income');

        // Obtener cuenta de IVA ventas si hay IVA
        $taxAccountId = null;
        $totalIva = $creditNote->iva_5 + $creditNote->iva_10;
        if ($totalIva > 0) {
            $taxAccountId = AccountingSetting::getValue($tenantId, 'sales_tax');
        }

        // Crear el asiento contable (reversión de la venta)
        DB::beginTransaction();
        try {
            $period = date('Y-m', strtotime($creditNote->date));

            $journalEntry = JournalEntry::create([
                'tenant_id' => $tenantId,
                'entry_number' => JournalEntry::generateEntryNumber($tenantId, $period),
                'entry_date' => $creditNote->date,
                'period' => $period,
                'reference' => $creditNote->credit_note_number,
                'description' => $this->getCreditNoteDescription($creditNote),
                'status' => 'posted',
                'user_id' => $creditNote->created_by,
            ]);

            // Calcular subtotal sin IVA
            $subtotalWithoutTax = $creditNote->subtotal_0 + $creditNote->subtotal_5 + $creditNote->subtotal_10;

            // Línea de débito: Ingresos por Ventas (reversión - reduce ingresos)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $debitAccountId,
                'description' => 'NC ' . $creditNote->credit_note_number . ' - ' . $sale->sale_number,
                'debit' => $subtotalWithoutTax,
                'credit' => 0,
            ]);

            // Si hay IVA, agregar línea de débito para IVA (reversión)
            if ($totalIva > 0 && $taxAccountId) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $taxAccountId,
                    'description' => 'IVA NC ' . $creditNote->credit_note_number,
                    'debit' => $totalIva,
                    'credit' => 0,
                ]);
            }

            // Línea de crédito: Caja/Banco/Cuentas por Cobrar (reversión - devuelve dinero o reduce deuda)
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $creditAccountId,
                'description' => $this->getCreditNoteDescription($creditNote),
                'debit' => 0,
                'credit' => $creditNote->total,
            ]);

            // Actualizar saldos de cuentas
            $journalEntry->updateAccountBalances();

            DB::commit();

            return $journalEntry;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validar que las cuentas necesarias para notas de crédito estén configuradas
     */
    private function validateCreditNoteAccounts(int $tenantId, Sale $sale): void
    {
        $errors = [];

        // Validar cuenta de ingresos
        if (!AccountingSetting::getValue($tenantId, 'sales_income')) {
            $errors[] = 'Cuenta de Ingresos por Ventas';
        }

        // Validar cuenta según método de pago
        if ($sale->payment_type === 'credit') {
            if (!AccountingSetting::getValue($tenantId, 'accounts_receivable')) {
                $errors[] = 'Cuenta de Cuentas por Cobrar';
            }
        } else {
            // Venta al contado
            if ($sale->payment_method === 'cash') {
                if (!AccountingSetting::getValue($tenantId, 'cash')) {
                    $errors[] = 'Cuenta de Caja';
                }
            } elseif (in_array($sale->payment_method, ['card', 'transfer'])) {
                if (!AccountingSetting::getValue($tenantId, 'bank_default')) {
                    $errors[] = 'Cuenta de Banco por Defecto';
                }
            }
        }

        // Validar cuenta de IVA si hay IVA
        $totalIva = $sale->iva_5 + $sale->iva_10;
        if ($totalIva > 0) {
            if (!AccountingSetting::getValue($tenantId, 'sales_tax')) {
                $errors[] = 'Cuenta de IVA Ventas';
            }
        }

        if (!empty($errors)) {
            throw new \Exception(
                'Debe configurar las siguientes cuentas contables antes de confirmar la nota de crédito: ' .
                implode(', ', $errors) . '. ' .
                'Vaya a Contabilidad > Configuración Contable para configurarlas.'
            );
        }
    }

    /**
     * Obtener descripción del asiento de nota de crédito
     */
    private function getCreditNoteDescription(CreditNote $creditNote): string
    {
        $description = 'Nota de Crédito ' . $creditNote->credit_note_number;
        $description .= ' - Ref: ' . $creditNote->sale->sale_number;

        if ($creditNote->customer) {
            $description .= ' - ' . $creditNote->customer->name;
        }

        return $description;
    }
}
