<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventItem;
use App\Models\EventGuest;
use App\Models\EventTable;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Mostrar listado de eventos
     */
    public function index()
    {
        return view('events.index');
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

        $query = Event::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });

        $total = $query->count();

        $items = $query->orderBy($sort, $order)
            ->skip(($page - 1) * $rows)
            ->take($rows)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'event_date' => $item->event_date->format('d/m/Y'),
                    'estimated_budget' => number_format($item->estimated_budget, 0, ',', '.'),
                    'status' => $item->status,
                    'status_label' => $item->status_label,
                ];
            });

        return response()->json([
            'total' => $total,
            'rows' => $items
        ]);
    }

    public function itemsData(Event $event)
    {
        $items = $event->items()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'amount' => number_format($item->estimated_unit_price, 0, ',', '.'),
                'raw_amount' => $item->estimated_unit_price,
                'notes' => $item->notes,
                'count_guests' => $item->count_guests,
            ];
        });
        return response()->json($items);
    }

    public function tablesGridData(Event $event)
    {
        $tables = $event->tables()->with(['budgets.guests'])->get()->map(function ($table) {
            // Sumar invitados de los presupuestos asignados (Familias) para el detalle
            $budgets = $table->budgets->map(function ($b) {
                return [
                    'id' => $b->id,
                    'family_name' => $b->family_name,
                    'guests_count' => $b->guests()->count(),
                    'guests' => $b->guests->map(function ($g) {
                        return ['name' => $g->name, 'cedula' => $g->cedula];
                    })
                ];
            });

            return [
                'id' => $table->id,
                'name' => $table->name,
                'capacity' => $table->capacity, // Usa el accessor del modelo
                'color' => $table->color,       // Usa el accessor del modelo
                'assigned_budgets' => $budgets
            ];
        });
        return response()->json($tables);
    }

    public function downloadTableReport(Event $event)
    {
        $event->load(['tables.budgets.guests']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.event-tables', compact('event'));
        return $pdf->stream('distribucion_mesas_' . $event->id . '.pdf');
    }

    public function unassignedBudgetsData(Event $event)
    {
        // Presupuestos Aceptados que NO tienen mesa asignada
        $budgets = $event->budgets()
            ->where('status', 'accepted')
            ->whereNull('table_id')
            ->withCount('guests')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'family_name' => $b->family_name,
                    'guests_count' => $b->guests_count
                ];
            });

        return response()->json(['rows' => $budgets, 'total' => $budgets->count()]);
    }

    public function assignBudgetToTable(Request $request, Event $event)
    {
        $request->validate([
            'budget_id' => 'required|exists:event_budgets,id',
            'table_id' => 'nullable|exists:event_tables,id' // Nullable to allow unassigning
        ]);

        $budget = \App\Models\EventBudget::find($request->budget_id);

        // Verificar que el presupuesto pertenezca al evento
        if ($budget->event_id != $event->id) {
            abort(403, 'El presupuesto no pertenece a este evento');
        }

        $budget->table_id = $request->table_id;
        $budget->save();

        return response()->json(['success' => true]);
    }

    /**
     * Guardar nuevo evento
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'estimated_budget' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $event = Event::create([
                'tenant_id' => Auth::user()->tenant_id,
                'name' => $request->name,
                'event_date' => $request->event_date,
                'estimated_budget' => $request->estimated_budget ?? 0,
                'notes' => $request->notes,
                'status' => 'draft',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Evento creado correctamente',
                'data' => $event
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['general' => ['Error al crear el evento: ' . $e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Ver detalle del evento
     */
    public function show(Event $event)
    {
        // Cargar relaciones
        $event->load(['items.product', 'tables.guests', 'guests.table']);
        return view('events.detail', compact('event'));
    }

    /**
     * Actualizar evento
     */
    public function update(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'event_date' => 'required|date',
            'estimated_budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,confirmed,cancelled,completed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $event->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Evento actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['general' => ['Error al actualizar el evento: ' . $e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Eliminar evento
     */
    public function destroy(Event $event)
    {
        if ($event->status !== 'draft') {
            return response()->json([
                'errors' => ['general' => ['Solo se pueden eliminar eventos en borrador']]
            ], 422);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evento eliminado correctamente'
        ]);
    }

    // --- Gestión de Presupuesto ---

    /**
     * Agregar item manualmente al presupuesto del evento
     */
    public function addItem(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'estimated_unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'count_guests' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $item = EventItem::create([
                'event_id' => $event->id,
                'description' => $request->description,
                'quantity' => 1,
                'estimated_unit_price' => $request->estimated_unit_price,
                'notes' => $request->notes,
                'count_guests' => $request->boolean('count_guests'),
                'total' => $request->estimated_unit_price // Ya que qty es 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item agregado correctamente',
                'item' => $item,
                'total_items' => number_format($event->items()->sum('total'), 0, ',', '.')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Agregar items del catálogo al presupuesto del evento
     */
    public function addItemsFromCatalog(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->product_ids as $productId) {
                $product = Product::find($productId);

                // Evitar duplicados si se prefiere, o simplemente agregar
                EventItem::create([
                    'event_id' => $event->id,
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'quantity' => 1,
                    'estimated_unit_price' => $product->sale_price,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Items agregados al presupuesto',
                'total_items' => number_format($event->items()->sum('total'), 0, ',', '.')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Actualizar item del presupuesto
     */
    public function updateBudgetItem(Request $request, EventItem $item)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.01',
            'estimated_unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Item actualizado',
            'total_items' => number_format($item->event->items()->sum('total'), 0, ',', '.')
        ]);
    }

    /**
     * Eliminar item del presupuesto
     */
    public function removeBudgetItem(EventItem $item)
    {
        $event = $item->event;
        $item->delete();
        return response()->json([
            'success' => true,
            'message' => 'Item eliminado',
            'total_items' => number_format($event->items()->sum('total'), 0, ',', '.')
        ]);
    }

    // --- Gestión de Invitados ---

    /**
     * Obtener mesas de un evento para combobox
     */
    public function tablesData(Event $event)
    {
        return response()->json($event->tables()->get(['id', 'name as text']));
    }

    /**
     * Validar invitado (Check-in)
     */
    public function validateGuest(EventGuest $guest)
    {
        $guest->is_validated = !$guest->is_validated;
        $guest->save();

        return response()->json([
            'success' => true,
            'message' => $guest->is_validated ? 'Invitado validado' : 'Validación revertida',
            'is_validated' => $guest->is_validated
        ]);
    }

    // --- Gestión de Mesas ---

    /**
     * Agregar mesa
     */
    public function addTable(Request $request, Event $event)
    {
        $request->validate(['name' => 'required|string']);

        $table = EventTable::create([
            'event_id' => $event->id,
            'name' => $request->name,
            'capacity' => 0, // Se calcula dinámicamente en tablesGridData
            'color' => '#e0e0e0' // Se calcula dinámicamente en tablesGridData
        ]);

        return response()->json(['success' => true, 'table' => $table]);
    }

    public function updateTable(Request $request, EventTable $table)
    {
        $request->validate(['name' => 'required|string']);

        $table->update([
            'name' => $request->name
        ]);

        return response()->json(['success' => true]);
    }

    public function removeTable(EventTable $table)
    {
        $table->delete();
        return response()->json(['success' => true]);
    }
}
