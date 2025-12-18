<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Customer;
use App\Models\CashRegister;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CompanySetting;
use App\Services\AccountingIntegrationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContributionController extends Controller
{
    /**
     * Mostrar listado de aportes
     */
    public function index()
    {
        return view('contributions.index');
    }

    /**
     * Obtener datos para el DataGrid
     */
    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Contribution::with(['customer', 'user'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('contribution_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            });

        $total = $query->count();

        $items = $query->orderBy($sort, $order)
            ->skip(($page - 1) * $rows)
            ->take($rows)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'contribution_number' => $item->contribution_number,
                    'contribution_date' => $item->contribution_date->format('d/m/Y'),
                    'customer_name' => $item->customer ? $item->customer->name : 'Sin alumno',
                    'user_name' => $item->user->name,
                    'amount' => number_format($item->amount, 0, ',', '.'),
                    'status' => $item->status,
                    'status_label' => $item->status_label,
                    'payment_method' => $item->payment_method,
                    'reference' => $item->reference,
                ];
            });

        return response()->json([
            'total' => $total,
            'rows' => $items
        ]);
    }

    /**
     * Guardar nuevo aporte (en borrador)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'contribution_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:Efectivo,Transferencia',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $item = Contribution::create([
                'tenant_id' => Auth::user()->tenant_id,
                'customer_id' => $request->customer_id,
                'user_id' => Auth::id(),
                'contribution_number' => Contribution::generateContributionNumber(Auth::user()->tenant_id),
                'contribution_date' => $request->contribution_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'status' => 'draft',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aporte creado correctamente',
                'data' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['general' => ['Error al crear el aporte: ' . $e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Confirmar aporte (impactar caja/banco y contabilidad)
     */
    public function confirm(Contribution $contribution)
    {
        if ($contribution->status !== 'draft') {
            return response()->json([
                'errors' => ['general' => ['Solo se pueden confirmar aportes en borrador']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Validar y registrar en Caja (si es Efectivo)
            if ($contribution->payment_method === 'Efectivo') {
                $cashRegister = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());

                if (!$cashRegister) {
                    throw new \Exception('Debes tener una caja abierta para confirmar aportes en efectivo');
                }

                // Registrar movimiento en caja
                $cashRegister->movements()->create([
                    'type' => 'income',
                    'concept' => 'other',
                    'amount' => $contribution->amount,
                    'description' => 'Aporte ' . $contribution->contribution_number . ' - ' . $contribution->customer->name,
                    'reference' => $contribution->contribution_number,
                    'contribution_id' => $contribution->id,
                ]);

                // Actualizar totales de caja
                $cashRegister->collections += $contribution->amount;
                $cashRegister->calculateExpectedBalance();
                $cashRegister->save();
            }

            // 2. Registrar en Banco (si es Transferencia)
            if ($contribution->payment_method === 'Transferencia') {
                $defaultAccount = BankAccount::getDefaultAccount(Auth::user()->tenant_id);

                if (!$defaultAccount) {
                    throw new \Exception('Debe configurar una cuenta bancaria predeterminada para registrar transferencias');
                }

                // Calcular el balance después de la transacción
                $balanceAfter = $defaultAccount->current_balance + $contribution->amount;

                // Crear transacción bancaria
                BankTransaction::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'bank_account_id' => $defaultAccount->id,
                    'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                    'transaction_date' => $contribution->contribution_date,
                    'type' => 'deposit',
                    'amount' => $contribution->amount,
                    'concept' => 'Aporte por transferencia',
                    'description' => 'Aporte ' . $contribution->contribution_number . ' - ' . $contribution->customer->name,
                    'reference' => $contribution->contribution_number,
                    'balance_after' => $balanceAfter,
                    'user_id' => Auth::id(),
                    'status' => 'completed',
                    'reconciled' => false,
                ]);
            }

            // 3. Crear asiento contable
            $accountingService = new AccountingIntegrationService();
            $accountingService->createContributionJournalEntry($contribution);

            // 4. Actualizar estado
            $contribution->status = 'confirmed';
            $contribution->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aporte confirmado correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Anular aporte
     */
    public function cancel(Contribution $contribution)
    {
        if ($contribution->status === 'cancelled') {
            return response()->json(['errors' => ['general' => ['El aporte ya está anulado']]], 422);
        }

        try {
            DB::beginTransaction();

            if ($contribution->status === 'confirmed') {
                // 1. Reversar en Caja (si fue Efectivo)
                if ($contribution->payment_method === 'Efectivo') {
                    $cashRegister = CashRegister::getUserRegisterForDate(
                        Auth::user()->tenant_id,
                        Auth::id(),
                        $contribution->contribution_date->format('Y-m-d')
                    );

                    if ($cashRegister && $cashRegister->status === 'open') {
                        $cashRegister->movements()->create([
                            'type' => 'expense',
                            'concept' => 'other',
                            'amount' => $contribution->amount,
                            'description' => 'Anulación de aporte ' . $contribution->contribution_number,
                            'reference' => $contribution->contribution_number,
                            'contribution_id' => $contribution->id,
                        ]);

                        $cashRegister->collections -= $contribution->amount;
                        $cashRegister->calculateExpectedBalance();
                        $cashRegister->save();
                    }
                }

                // 2. Reversar en Banco (si fue Transferencia)
                if ($contribution->payment_method === 'Transferencia') {
                    $bankTransaction = BankTransaction::where('tenant_id', Auth::user()->tenant_id)
                        ->where('reference', $contribution->contribution_number)
                        ->where('type', 'deposit')
                        ->where('status', 'completed')
                        ->first();

                    if ($bankTransaction) {
                        $bankTransaction->status = 'cancelled';
                        $bankTransaction->save();
                        $bankTransaction->bankAccount->updateBalance();
                    }
                }

                // 3. Reversar asiento contable
                if ($contribution->journal_entry_id) {
                    $accountingService = new AccountingIntegrationService();
                    $accountingService->reverseContributionJournalEntry($contribution);
                }
            }

            // 4. Actualizar estado
            $contribution->status = 'cancelled';
            $contribution->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aporte anulado correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => ['Error al anular el aporte: ' . $e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Generar PDF del recibo
     */
    public function generateReceipt(Contribution $contribution)
    {
        if ($contribution->status !== 'confirmed') {
            abort(403, 'Solo se pueden generar recibos de aportes confirmados.');
        }

        // Verificar pertenencia al tenant
        if ($contribution->tenant_id != Auth::user()->tenant_id) {
            abort(403);
        }

        $contribution->load(['customer', 'user']);
        $companySettings = CompanySetting::where('tenant_id', Auth::user()->tenant_id)->first();

        $pdf = Pdf::loadView('pdf.contribution-receipt', compact('contribution', 'companySettings'));

        return $pdf->stream('recibo-' . $contribution->contribution_number . '.pdf');
    }

    /**
     * Eliminar aporte (solo borradores)
     */
    public function destroy(Contribution $contribution)
    {
        if ($contribution->status !== 'draft') {
            return response()->json(['errors' => ['general' => ['Solo se pueden eliminar aportes en borrador']]], 422);
        }

        $contribution->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aporte eliminado correctamente'
        ]);
    }
}
