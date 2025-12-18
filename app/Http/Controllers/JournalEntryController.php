<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\AccountChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index()
    {
        return view('accounting.journal-entries.index');
    }

    public function data(Request $request)
    {
        $query = JournalEntry::with('user')
            ->where('tenant_id', Auth::user()->tenant_id);

        // Filtros
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('entry_type') && $request->entry_type !== '') {
            $query->where('entry_type', $request->entry_type);
        }

        if ($request->has('period') && $request->period) {
            $query->where('period', $request->period);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('entry_number', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Paginación y ordenamiento
        $sort = $request->sort ?? 'entry_date';
        $order = $request->order ?? 'desc';
        $query->orderBy($sort, $order);

        $total = $query->count();
        $rows = $query->skip($request->offset ?? 0)
            ->take($request->limit ?? 20)
            ->get()
            ->map(function($entry) {
                return [
                    'id' => $entry->id,
                    'entry_number' => $entry->entry_number,
                    'entry_date' => $entry->entry_date->format('Y-m-d'),
                    'period' => $entry->period,
                    'entry_type' => $entry->entry_type,
                    'status' => $entry->status,
                    'description' => $entry->description,
                    'total_debit' => $entry->total_debit,
                    'total_credit' => $entry->total_credit,
                    'is_balanced' => $entry->is_balanced,
                    'user_name' => $entry->user->name,
                    'posted_at' => $entry->posted_at ? $entry->posted_at->format('Y-m-d H:i') : null,
                ];
            });

        return response()->json([
            'total' => $total,
            'rows' => $rows
        ]);
    }

    public function create()
    {
        return view('accounting.journal-entries.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:account_chart,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generar número de asiento
            $period = date('Y-m', strtotime($request->entry_date));
            $entryNumber = JournalEntry::generateEntryNumber(Auth::user()->tenant_id, $period);

            // Crear asiento
            $entry = JournalEntry::create([
                'tenant_id' => Auth::user()->tenant_id,
                'user_id' => Auth::id(),
                'entry_number' => $entryNumber,
                'entry_date' => $request->entry_date,
                'period' => $period,
                'entry_type' => 'manual',
                'status' => 'draft',
                'description' => $request->description,
                'notes' => $request->notes,
            ]);

            // Crear líneas
            foreach ($request->lines as $lineData) {
                if ($lineData['debit'] > 0 || $lineData['credit'] > 0) {
                    JournalEntryLine::create([
                        'tenant_id' => Auth::user()->tenant_id,
                        'journal_entry_id' => $entry->id,
                        'account_id' => $lineData['account_id'],
                        'description' => $lineData['description'] ?? null,
                        'debit' => $lineData['debit'],
                        'credit' => $lineData['credit'],
                    ]);
                }
            }

            // Calcular totales
            $entry->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asiento creado exitosamente',
                'id' => $entry->id,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el asiento: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $entry = JournalEntry::with(['lines.account', 'user'])
            ->findOrFail($id);

        return view('accounting.journal-entries.show', compact('entry'));
    }

    public function edit($id)
    {
        $entry = JournalEntry::with(['lines.account'])
            ->findOrFail($id);

        if ($entry->status !== 'draft') {
            return redirect()->route('journal-entries.show', $id)
                ->with('error', 'Solo se pueden editar asientos en borrador');
        }

        return view('accounting.journal-entries.edit', compact('entry'));
    }

    public function update(Request $request, $id)
    {
        $entry = JournalEntry::findOrFail($id);

        if ($entry->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar asientos en borrador',
            ], 400);
        }

        $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:account_chart,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Actualizar asiento
            $period = date('Y-m', strtotime($request->entry_date));
            $entry->update([
                'entry_date' => $request->entry_date,
                'period' => $period,
                'description' => $request->description,
                'notes' => $request->notes,
            ]);

            // Eliminar líneas existentes
            $entry->lines()->delete();

            // Crear nuevas líneas
            foreach ($request->lines as $lineData) {
                if ($lineData['debit'] > 0 || $lineData['credit'] > 0) {
                    JournalEntryLine::create([
                        'tenant_id' => Auth::user()->tenant_id,
                        'journal_entry_id' => $entry->id,
                        'account_id' => $lineData['account_id'],
                        'description' => $lineData['description'] ?? null,
                        'debit' => $lineData['debit'],
                        'credit' => $lineData['credit'],
                    ]);
                }
            }

            // Calcular totales
            $entry->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Asiento actualizado exitosamente',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el asiento: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $entry = JournalEntry::findOrFail($id);

        if ($entry->status === 'posted') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un asiento contabilizado. Debe anularlo primero.',
            ], 400);
        }

        $entry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asiento eliminado exitosamente',
        ]);
    }

    /**
     * Contabilizar asiento
     */
    public function post($id)
    {
        $entry = JournalEntry::findOrFail($id);

        try {
            $entry->post();

            return response()->json([
                'success' => true,
                'message' => 'Asiento contabilizado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Anular asiento
     */
    public function cancel($id)
    {
        $entry = JournalEntry::findOrFail($id);

        try {
            $entry->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Asiento anulado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
