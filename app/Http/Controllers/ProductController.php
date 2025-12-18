<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Product::with('category')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $products = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'category' => $product->category?->name,
                'barcode' => $product->barcode,
                'unit' => $product->unit,
                'purchase_price' => number_format($product->purchase_price, 0, ',', '.'),
                'sale_price' => number_format($product->sale_price, 0, ',', '.'),
                'stock' => number_format($product->stock, 2, ',', '.'),
                'is_active' => $product->is_active,
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

        $products = Product::where('is_active', true)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'code', 'name', 'purchase_price', 'sale_price', 'stock', 'tax_rate']);

        // Agregar cost_price como alias de purchase_price para las compras
        $products = $products->map(function ($product) {
            $product->cost_price = $product->purchase_price;
            return $product;
        });

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:products,code',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'barcode' => 'nullable|string|max:50',
            'unit' => 'required|string|max:20',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|in:0,5,10',
            'stock' => 'nullable|numeric',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'track_stock' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => $product,
        ]);
    }

    public function show(Product $product)
    {
        $product->load('category');
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:products,code,' . $product->id,
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'barcode' => 'nullable|string|max:50',
            'unit' => 'required|string|max:20',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|in:0,5,10',
            'stock' => 'nullable|numeric',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'track_stock' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'data' => $product,
        ]);
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el producto.',
            ], 400);
        }
    }
}
