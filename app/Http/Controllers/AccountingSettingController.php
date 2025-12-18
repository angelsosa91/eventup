<?php

namespace App\Http\Controllers;

use App\Models\AccountingSetting;
use App\Models\AccountChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountingSettingController extends Controller
{
    public function index()
    {
        $settings = AccountingSetting::where('tenant_id', Auth::user()->tenant_id)
            ->with('account')
            ->get()
            ->keyBy('key');

        $availableKeys = AccountingSetting::getAvailableKeys();

        return view('accounting.settings.index', compact('settings', 'availableKeys'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|exists:account_chart,id',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $availableKeys = AccountingSetting::getAvailableKeys();

        foreach ($request->settings as $key => $accountId) {
            if (array_key_exists($key, $availableKeys)) {
                if ($accountId) {
                    AccountingSetting::setValue(
                        $tenantId,
                        $key,
                        $accountId,
                        $availableKeys[$key]
                    );
                } else {
                    // Si no hay account_id, eliminar la configuración
                    AccountingSetting::where('tenant_id', $tenantId)
                        ->where('key', $key)
                        ->delete();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Configuración contable guardada exitosamente',
        ]);
    }
}
