<?php

namespace App\Http\Controllers;

use App\Models\TaxSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxSettingController extends Controller
{
    /**
     * Mostrar configuración de impuestos
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        // Inicializar impuestos por defecto si no existen
        if (TaxSetting::where('tenant_id', $tenantId)->count() == 0) {
            TaxSetting::initializeDefaults($tenantId);
        }

        $taxes = TaxSetting::where('tenant_id', $tenantId)
            ->orderBy('rate', 'desc')
            ->get();

        return view('settings.taxes', compact('taxes'));
    }

    /**
     * Crear nuevo impuesto
     */
    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'code' => 'nullable|string|max:10',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Si se marca como predeterminado, desmarcar otros
        if ($validated['is_default'] ?? false) {
            TaxSetting::where('tenant_id', $tenantId)
                ->update(['is_default' => false]);
        }

        $validated['tenant_id'] = $tenantId;

        TaxSetting::create($validated);

        return redirect()->route('settings.taxes')
            ->with('success', 'Impuesto creado correctamente');
    }

    /**
     * Actualizar impuesto
     */
    public function update(Request $request, TaxSetting $taxSetting)
    {
        $tenantId = Auth::user()->tenant_id;

        // Verificar pertenencia al tenant
        if ($taxSetting->tenant_id != $tenantId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'code' => 'nullable|string|max:10',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Si se marca como predeterminado, desmarcar otros
        if ($validated['is_default'] ?? false) {
            TaxSetting::where('tenant_id', $tenantId)
                ->where('id', '!=', $taxSetting->id)
                ->update(['is_default' => false]);
        }

        $taxSetting->update($validated);

        return redirect()->route('settings.taxes')
            ->with('success', 'Impuesto actualizado correctamente');
    }

    /**
     * Eliminar impuesto
     */
    public function destroy(TaxSetting $taxSetting)
    {
        $tenantId = Auth::user()->tenant_id;

        // Verificar pertenencia al tenant
        if ($taxSetting->tenant_id != $tenantId) {
            abort(403);
        }

        $taxSetting->delete();

        return redirect()->route('settings.taxes')
            ->with('success', 'Impuesto eliminado correctamente');
    }
}
