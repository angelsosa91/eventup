<?php

namespace App\Http\Controllers;

use App\Models\AcademicGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AcademicGradeController extends Controller
{
    public function index()
    {
        return view('academic.grades.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = AcademicGrade::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $items = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $items->map(function ($item) {
            /** @var \App\Models\AcademicGrade $item */
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
        $items = AcademicGrade::where('is_active', true)
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

        $item = AcademicGrade::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Curso creado exitosamente',
            'data' => $item,
        ]);
    }

    public function show(AcademicGrade $academicGrade)
    {
        return response()->json($academicGrade);
    }

    public function update(Request $request, AcademicGrade $academicGrade)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $academicGrade->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Curso actualizado exitosamente',
            'data' => $academicGrade,
        ]);
    }

    public function destroy(AcademicGrade $academicGrade)
    {
        if ($academicGrade->students()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el curso. Tiene alumnos asociados.',
            ], 400);
        }

        $academicGrade->delete();

        return response()->json([
            'success' => true,
            'message' => 'Curso eliminado exitosamente',
        ]);
    }
}
