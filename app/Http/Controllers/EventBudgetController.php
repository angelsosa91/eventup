<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Event;
use App\Models\EventBudget;
use App\Models\EventBudgetItem;
use App\Models\EventBudgetGuest;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventBudgetController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return view('event-budgets.index', compact('events'));
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $event_id = $request->get('event_id');
        $search = $request->get('search');

        $query = EventBudget::with(['event', 'customer'])
            ->when($event_id, function ($q) use ($event_id) {
                $q->where('event_id', $event_id);
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('family_name', 'like', "%{$search}%");
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
                    'event_name' => $item->event->name,
                    'customer_id' => $item->customer_id,
                    'customer_name' => $item->customer->name,
                    'family_name' => $item->family_name ?: $item->customer->family_name,
                    'budget_date' => $item->budget_date->format('d/m/Y'),
                    'total_amount' => number_format($item->total_amount, 0, ',', '.'),
                    'status' => $item->status,
                    'status_label' => $item->status_label,
                ];
            });

        return response()->json([
            'total' => $total,
            'rows' => $items
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'customer_id' => 'required|exists:customers,id',
            'budget_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $customer = Customer::find($request->customer_id);

            $budget = EventBudget::create([
                'tenant_id' => Auth::user()->tenant_id,
                'event_id' => $request->event_id,
                'customer_id' => $request->customer_id,
                'family_name' => $customer->family_name,
                'budget_date' => $request->budget_date,
                'status' => 'draft',
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto creado con éxito',
                'data' => $budget
            ]);

        } catch (\Exception $e) {
            return response()->json(['errors' => ['general' => [$e->getMessage()]]], 422);
        }
    }

    public function show(EventBudget $eventBudget)
    {
        $eventBudget->load(['event', 'customer', 'items', 'guests.table']);
        return view('event-budgets.detail', compact('eventBudget'));
    }

    public function update(Request $request, EventBudget $eventBudget)
    {
        $validator = Validator::make($request->all(), [
            'budget_date' => 'required|date',
            'status' => 'required|in:draft,sent,accepted,rejected',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $eventBudget->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Presupuesto actualizado'
        ]);
    }

    public function destroy(EventBudget $eventBudget)
    {
        $eventBudget->delete();
        return response()->json(['success' => true, 'message' => 'Presupuesto eliminado']);
    }

    // --- Items del Presupuesto ---

    public function addItem(Request $request, EventBudget $eventBudget)
    {
        $request->validate([
            'description' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $item = $eventBudget->items()->create([
            'description' => $request->description,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'notes' => $request->notes,
        ]);

        return response()->json(['success' => true, 'item' => $item, 'total_budget' => $eventBudget->total_amount]);
    }

    public function updateItem(Request $request, EventBudgetItem $item)
    {
        $item->update($request->all());
        return response()->json(['success' => true, 'item' => $item, 'total_budget' => $item->budget->total_amount]);
    }

    public function removeItem(EventBudgetItem $item)
    {
        $budget = $item->budget;
        $item->delete();
        return response()->json(['success' => true, 'total_budget' => $budget->total_amount]);
    }

    // --- Invitados del Presupuesto ---

    public function addGuest(Request $request, EventBudget $eventBudget)
    {
        $request->validate(['name' => 'required|string']);

        $guest = $eventBudget->guests()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'table_id' => $request->table_id,
        ]);

        return response()->json(['success' => true, 'guest' => $guest]);
    }

    public function updateGuest(Request $request, EventBudgetGuest $guest)
    {
        $guest->update($request->all());
        return response()->json(['success' => true]);
    }

    public function removeGuest(EventBudgetGuest $guest)
    {
        $guest->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Generar PDF del presupuesto
     */
    public function generatePDF(EventBudget $eventBudget)
    {
        // Verificar pertenencia al tenant
        if ($eventBudget->tenant_id != Auth::user()->tenant_id) {
            abort(403);
        }

        $eventBudget->load(['event', 'customer', 'items', 'guests.table']);
        $companySettings = CompanySetting::where('tenant_id', Auth::user()->tenant_id)->first();

        $pdf = Pdf::loadView('pdf.event-budget', compact('eventBudget', 'companySettings'));

        return $pdf->stream('presupuesto-' . $eventBudget->id . '.pdf');
    }
}
