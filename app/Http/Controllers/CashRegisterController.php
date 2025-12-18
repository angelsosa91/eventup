<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashRegisterMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashRegisterController extends Controller
{
    public function index()
    {
        return view('cash-registers.index');
    }

    public function data(Request $request)
    {
        $query = CashRegister::with('user')
            ->where('user_id', Auth::id());

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('register_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'desc';
        $query->orderBy($sort, $order);

        $total = $query->count();
        $rows = $query->skip($request->offset ?? 0)
            ->take($request->limit ?? 20)
            ->get()
            ->map(function($register) {
                return [
                    'id' => $register->id,
                    'register_number' => $register->register_number,
                    'register_date' => $register->register_date->format('d/m/Y'),
                    'user_name' => $register->user->name,
                    'opening_balance' => $register->opening_balance,
                    'expected_balance' => $register->expected_balance,
                    'actual_balance' => $register->actual_balance,
                    'difference' => $register->difference,
                    'status' => $register->status,
                ];
            });

        return response()->json([
            'total' => $total,
            'rows' => $rows
        ]);
    }

    public function current()
    {
        $register = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());

        if (!$register) {
            return view('cash-registers.open');
        }

        $register->load(['movements' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('cash-registers.current', compact('register'));
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        // Verificar que no haya una caja abierta para este usuario
        $existingRegister = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());
        if ($existingRegister) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una caja abierta para hoy'
            ], 400);
        }

        $register = CashRegister::create([
            'tenant_id' => Auth::user()->tenant_id,
            'register_number' => CashRegister::generateRegisterNumber(Auth::user()->tenant_id),
            'register_date' => date('Y-m-d'),
            'user_id' => Auth::id(),
            'opening_balance' => $request->opening_balance,
            'expected_balance' => $request->opening_balance,
            'status' => 'open',
            'opened_at' => now(),
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Caja abierta exitosamente',
            'id' => $register->id
        ]);
    }

    public function addMovement(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:income,expense',
            'concept' => 'required|in:sale,collection,payment,expense,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $register = CashRegister::findOrFail($id);

        // Verificar que el usuario sea el dueño de la caja
        if ($register->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para modificar esta caja'
            ], 403);
        }

        if ($register->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'La caja está cerrada'
            ], 400);
        }

        DB::beginTransaction();
        try {
            CashRegisterMovement::create([
                'cash_register_id' => $register->id,
                'type' => $request->type,
                'concept' => $request->concept,
                'description' => $request->description,
                'amount' => $request->amount,
                'reference' => $request->reference,
            ]);

            // Actualizar totales según el concepto
            switch ($request->concept) {
                case 'sale':
                    $register->sales_cash += $request->amount;
                    break;
                case 'collection':
                    $register->collections += $request->amount;
                    break;
                case 'payment':
                    $register->payments += $request->amount;
                    break;
                case 'expense':
                    $register->expenses += $request->amount;
                    break;
            }

            $register->calculateExpectedBalance();
            $register->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento registrado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function close(Request $request, $id)
    {
        $request->validate([
            'actual_balance' => 'required|numeric|min:0',
        ]);

        $register = CashRegister::findOrFail($id);

        // Verificar que el usuario sea el dueño de la caja
        if ($register->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para cerrar esta caja'
            ], 403);
        }

        if ($register->status !== 'open') {
            return response()->json([
                'success' => false,
                'message' => 'La caja ya está cerrada'
            ], 400);
        }

        $register->actual_balance = $request->actual_balance;
        $register->calculateDifference();
        $register->status = 'closed';
        $register->closed_at = now();
        $register->save();

        return response()->json([
            'success' => true,
            'message' => 'Caja cerrada exitosamente'
        ]);
    }

    public function show($id)
    {
        $register = CashRegister::with(['user', 'movements' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])
        ->where('user_id', Auth::id())
        ->findOrFail($id);

        return view('cash-registers.detail', compact('register'));
    }
}
