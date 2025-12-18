<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    /**
     * Mostrar formulario de configuración
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $settings = CompanySetting::getOrCreate($tenantId);

        return view('settings.company', compact('settings'));
    }

    /**
     * Actualizar configuración de la empresa
     */
    public function update(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'slogan' => 'nullable|string|max:500',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
            'decimal_places' => 'required|integer|min:0|max:4',
            'date_format' => 'required|string|max:20',
            'timezone' => 'required|string|max:50',
            'invoice_requires_tax_id' => 'boolean',
            'low_stock_threshold' => 'required|integer|min:0',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $settings = CompanySetting::where('tenant_id', $tenantId)->first();

        if (!$settings) {
            $settings = new CompanySetting();
            $settings->tenant_id = $tenantId;
        }

        // Manejar la carga del logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            // Guardar nuevo logo
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $path;
        }

        $settings->fill($validated);
        $settings->save();

        return redirect()->route('settings.company')
            ->with('success', 'Configuración actualizada correctamente');
    }

    /**
     * Eliminar logo
     */
    public function deleteLogo()
    {
        $tenantId = Auth::user()->tenant_id;
        $settings = CompanySetting::where('tenant_id', $tenantId)->first();

        if ($settings && $settings->logo_path) {
            if (Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = null;
            $settings->save();
        }

        return redirect()->route('settings.company')
            ->with('success', 'Logo eliminado correctamente');
    }
}
