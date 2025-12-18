<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index()
    {
        return view('suppliers.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Supplier::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('ruc', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $suppliers = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $suppliers->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'ruc' => $supplier->ruc,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'city' => $supplier->city,
                'contact_person' => $supplier->contact_person,
                'payment_days' => $supplier->payment_days,
                'is_active' => $supplier->is_active,
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function list(Request $request)
    {
        $search = $request->get('q', '');

        $suppliers = Supplier::where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('ruc', 'like', "%{$search}%");
                });
            })
            ->select('id', 'name', 'ruc', 'phone')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'payment_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $supplier = Supplier::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Proveedor creado exitosamente',
            'data' => $supplier,
        ]);
    }

    public function show(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'payment_days' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $supplier->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Proveedor actualizado exitosamente',
            'data' => $supplier,
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el proveedor. Puede tener registros asociados.',
            ], 400);
        }
    }
}
