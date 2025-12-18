<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return view('roles.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'asc');

        $query = Role::withCount(['users', 'permissions'])
            ->orderBy($sort, $order);

        $total = $query->count();
        $roles = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'description' => $role->description,
                'users_count' => $role->users_count,
                'permissions_count' => $role->permissions_count,
                'is_system' => $role->is_system,
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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rol creado exitosamente',
            'data' => $role,
        ]);
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'is_system' => $role->is_system,
            'permissions' => $role->permissions->pluck('id')->toArray(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar un rol del sistema',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string',
            'permissions' => 'array',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado exitosamente',
            'data' => $role,
        ]);
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un rol del sistema',
            ], 422);
        }

        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un rol que tiene usuarios asignados',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado exitosamente',
        ]);
    }

    public function permissions()
    {
        $permissions = Permission::all()->groupBy('module');
        return response()->json($permissions);
    }

    public function list()
    {
        $roles = Role::select('id', 'name')->get();
        return response()->json($roles);
    }
}
