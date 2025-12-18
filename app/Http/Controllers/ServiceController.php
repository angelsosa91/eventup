<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index()
    {
        return view('services.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Product::where('type', 'service')
            ->with('category')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $items = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'category' => $item->category?->name,
                'unit' => $item->unit,
                'purchase_price' => number_format($item->purchase_price, 0, ',', '.'),
                'sale_price' => number_format($item->sale_price, 0, ',', '.'),
                'is_active' => $item->is_active,
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'code' => 'required|string|max:50|unique:products,code',
                'name' => 'required|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'unit' => 'required|string|max:20',
                'purchase_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'tax_rate' => 'required|in:0,5,10',
                'is_active' => 'boolean',
                'notes' => 'nullable|string',
            ],
            [
                'code.required' => 'El código es obligatorio.',
                'code.unique' => 'El código ya existe.',
                'name.required' => 'El nombre es obligatorio.',
                'category_id.exists' => 'La categoría no existe.',
                'description.string' => 'La descripción debe ser una cadena.',
                'unit.required' => 'La unidad es obligatoria.',
                'purchase_price.required' => 'El precio de compra es obligatorio.',
                'sale_price.required' => 'El precio de venta es obligatorio.',
                'tax_rate.required' => 'El tipo de IVA es obligatorio.',
                'tax_rate.in' => 'El tipo de IVA debe ser 0, 5 o 10.',
                'is_active.boolean' => 'El estado debe ser un booleano.',
                'notes.string' => 'Las notas deben ser una cadena.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['type'] = 'service';
        $data['track_stock'] = false;
        $data['stock'] = 0;

        $item = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Servicio creado exitosamente',
            'data' => $item,
        ]);
    }

    public function show(Product $service)
    {
        if ($service->type !== 'service') {
            return response()->json(['message' => 'Not a service'], 404);
        }
        $service->load('category');
        return response()->json($service);
    }

    public function update(Request $request, Product $service)
    {
        if ($service->type !== 'service') {
            return response()->json(['message' => 'Not a service'], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'code' => 'required|string|max:50|unique:products,code,' . $service->id,
                'name' => 'required|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'description' => 'nullable|string',
                'unit' => 'required|string|max:20',
                'purchase_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'tax_rate' => 'required|in:0,5,10',
                'is_active' => 'boolean',
                'notes' => 'nullable|string',
            ],
            [
                'code.required' => 'El código es obligatorio.',
                'code.unique' => 'El código ya existe.',
                'name.required' => 'El nombre es obligatorio.',
                'category_id.exists' => 'La categoría no existe.',
                'description.string' => 'La descripción debe ser una cadena.',
                'unit.required' => 'La unidad es obligatoria.',
                'purchase_price.required' => 'El precio de compra es obligatorio.',
                'sale_price.required' => 'El precio de venta es obligatorio.',
                'tax_rate.required' => 'El tipo de IVA es obligatorio.',
                'tax_rate.in' => 'El tipo de IVA debe ser 0, 5 o 10.',
                'is_active.boolean' => 'El estado debe ser un booleano.',
                'notes.string' => 'Las notas deben ser una cadena.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['type'] = 'service';
        $data['track_stock'] = false;
        // No tocamos el stock para servicios, debería ser 0 siempre

        $service->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Servicio actualizado exitosamente',
            'data' => $service,
        ]);
    }

    public function destroy(Product $service)
    {
        if ($service->type !== 'service') {
            return response()->json(['message' => 'Not a service'], 404);
        }

        try {
            $service->delete();
            return response()->json([
                'success' => true,
                'message' => 'Servicio eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el servicio.',
            ], 400);
        }
    }
}
