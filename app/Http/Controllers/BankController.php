<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BankController extends Controller
{
    public function index()
    {
        return view('banks.index');
    }

    public function list(Request $request)
    {
        $query = Bank::query();

        // Búsqueda general
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('short_name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('swift_code', 'like', '%' . $search . '%');
            });
        }

        // Filtro por estado
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        // Paginación y ordenamiento
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 20);
        $sort = $request->input('sort', 'name');
        $order = $request->input('order', 'asc');

        $query->orderBy($sort, $order);

        $total = $query->count();
        $banks = $query->skip(($page - 1) * $rows)->take($rows)->get();

        return response()->json([
            'total' => $total,
            'rows' => $banks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'code' => 'nullable|string|max:10|unique:banks,code',
            'swift_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('banks', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        $bank = Bank::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Banco creado correctamente',
            'data' => $bank,
        ]);
    }

    public function show(Bank $bank)
    {
        return response()->json($bank);
    }

    public function update(Request $request, Bank $bank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'code' => 'nullable|string|max:10|unique:banks,code,' . $bank->id,
            'swift_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($bank->logo) {
                Storage::disk('public')->delete($bank->logo);
            }
            $validated['logo'] = $request->file('logo')->store('banks', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        $bank->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Banco actualizado correctamente',
            'data' => $bank,
        ]);
    }

    public function destroy(Bank $bank)
    {
        if ($bank->bankAccounts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el banco porque tiene cuentas asociadas',
            ], 400);
        }

        if ($bank->logo) {
            Storage::disk('public')->delete($bank->logo);
        }

        $bank->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banco eliminado correctamente',
        ]);
    }

    public function getActive()
    {
        $banks = Bank::active()->orderBy('name')->get(['id', 'name', 'short_name', 'code']);

        return response()->json($banks);
    }
}
