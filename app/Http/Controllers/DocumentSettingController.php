<?php

namespace App\Http\Controllers;

use App\Models\DocumentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentSettingController extends Controller
{
    /**
     * Mostrar configuración de numeración de documentos
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $settings = DocumentSetting::where('tenant_id', $tenantId)
            ->orderBy('document_type')
            ->orderBy('series')
            ->get();

        $documentTypes = DocumentSetting::getAvailableDocumentTypes();

        return view('settings.documents', compact('settings', 'documentTypes'));
    }

    /**
     * Crear nueva configuración de documento
     */
    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $validated = $request->validate([
            'document_type' => 'required|string|max:50',
            'prefix' => 'nullable|string|max:10',
            'series' => 'nullable|string|max:10',
            'next_number' => 'required|integer|min:1',
            'padding' => 'required|integer|min:1|max:10',
            'format' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        // Si se activa esta configuración, desactivar otras del mismo tipo
        if ($validated['is_active'] ?? true) {
            DocumentSetting::where('tenant_id', $tenantId)
                ->where('document_type', $validated['document_type'])
                ->update(['is_active' => false]);
        }

        $validated['tenant_id'] = $tenantId;

        DocumentSetting::create($validated);

        return redirect()->route('settings.documents')
            ->with('success', 'Configuración de documento creada correctamente');
    }

    /**
     * Actualizar configuración existente
     */
    public function update(Request $request, DocumentSetting $documentSetting)
    {
        $tenantId = Auth::user()->tenant_id;

        // Verificar pertenencia al tenant
        if ($documentSetting->tenant_id != $tenantId) {
            abort(403);
        }

        $validated = $request->validate([
            'prefix' => 'nullable|string|max:10',
            'series' => 'nullable|string|max:10',
            'next_number' => 'required|integer|min:1',
            'padding' => 'required|integer|min:1|max:10',
            'format' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        // Si se activa esta configuración, desactivar otras del mismo tipo
        if ($validated['is_active'] ?? false) {
            DocumentSetting::where('tenant_id', $tenantId)
                ->where('document_type', $documentSetting->document_type)
                ->where('id', '!=', $documentSetting->id)
                ->update(['is_active' => false]);
        }

        $documentSetting->update($validated);

        return redirect()->route('settings.documents')
            ->with('success', 'Configuración actualizada correctamente');
    }

    /**
     * Eliminar configuración
     */
    public function destroy(DocumentSetting $documentSetting)
    {
        $tenantId = Auth::user()->tenant_id;

        // Verificar pertenencia al tenant
        if ($documentSetting->tenant_id != $tenantId) {
            abort(403);
        }

        $documentSetting->delete();

        return redirect()->route('settings.documents')
            ->with('success', 'Configuración eliminada correctamente');
    }
}
