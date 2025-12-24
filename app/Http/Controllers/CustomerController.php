<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Customer::with(['grade', 'section', 'delegate'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('ruc', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $customers = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'family_name' => $customer->family_name,
                'ruc' => $customer->ruc,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'mobile' => $customer->mobile,
                'address' => $customer->address,
                'grade_name' => $customer->grade?->name,
                'section_name' => $customer->section?->name,
                'delegate_name' => $customer->delegate?->name,
                'is_active' => $customer->is_active,
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'family_name' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'grade_id' => 'nullable|exists:academic_grades,id',
            'section_id' => 'nullable|exists:academic_sections,id',
            'shift_id' => 'nullable|exists:academic_shifts,id',
            'bachillerato_id' => 'nullable|exists:academic_bachilleratos,id',
            'delegate_id' => 'nullable|exists:delegates,id',
            'billing_name' => 'nullable|string|max:255',
            'billing_ruc' => 'nullable|string|max:50',
            'billing_email' => 'nullable|email|max:255',
            'budget_type' => 'nullable|string|in:unique,parents',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alumno/Cliente creado exitosamente',
            'data' => $customer,
        ]);
    }

    public function show(Customer $customer)
    {
        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'family_name' => 'nullable|string|max:255',
            'ruc' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'grade_id' => 'nullable|exists:academic_grades,id',
            'section_id' => 'nullable|exists:academic_sections,id',
            'shift_id' => 'nullable|exists:academic_shifts,id',
            'bachillerato_id' => 'nullable|exists:academic_bachilleratos,id',
            'delegate_id' => 'nullable|exists:delegates,id',
            'billing_name' => 'nullable|string|max:255',
            'billing_ruc' => 'nullable|string|max:50',
            'billing_email' => 'nullable|email|max:255',
            'budget_type' => 'nullable|string|in:unique,parents',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Alumno/Cliente actualizado exitosamente',
            'data' => $customer,
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alumno/Cliente eliminado exitosamente',
        ]);
    }

    public function list(Request $request)
    {
        $search = $request->get('q', '');

        $customers = Customer::where('is_active', true)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('ruc', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->select('id', 'name', 'ruc', 'phone')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($customers);
    }
}
