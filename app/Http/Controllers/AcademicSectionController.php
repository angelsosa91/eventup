<?php

namespace App\Http\Controllers;

use App\Models\AcademicSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AcademicSectionController extends Controller
{
    public function index()
    {
        return view('academic.sections.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = AcademicSection::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $items = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'is_active' => $item->is_active,
                'students_count' => $item->students()->count(),
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function list()
    {
        $items = AcademicSection::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = AcademicSection::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Sección creada exitosamente',
            'data' => $item,
        ]);
    }

    public function show(AcademicSection $academicSection)
    {
        return response()->json($academicSection);
    }

    public function update(Request $request, AcademicSection $academicSection)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $academicSection->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Sección actualizada exitosamente',
            'data' => $academicSection,
        ]);
    }

    public function destroy(AcademicSection $academicSection)
    {
        if ($academicSection->students()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la sección. Tiene alumnos asociados.',
            ], 400);
        }

        $academicSection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sección eliminada exitosamente',
        ]);
    }
}
