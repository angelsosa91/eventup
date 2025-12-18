<?php

namespace Database\Seeders;

use App\Models\AccountChart;
use Illuminate\Database\Seeder;

class AccountChartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Este seeder crea un plan de cuentas básico
        // NOTA: Solo ejecutar en tenant_id = 1 o ajustar según sea necesario

        $tenantId = 1;

        $accounts = [
            // 1. ACTIVO
            [
                'code' => '1',
                'name' => 'ACTIVO',
                'account_type' => 'asset',
                'nature' => 'debit',
                'level' => 1,
                'is_detail' => false,
                'children' => [
                    [
                        'code' => '1.1',
                        'name' => 'ACTIVO CORRIENTE',
                        'account_type' => 'asset',
                        'nature' => 'debit',
                        'level' => 2,
                        'is_detail' => false,
                        'children' => [
                            [
                                'code' => '1.1.01',
                                'name' => 'Caja',
                                'account_type' => 'asset',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '1.1.02',
                                'name' => 'Bancos',
                                'account_type' => 'asset',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '1.1.03',
                                'name' => 'Cuentas por Cobrar',
                                'account_type' => 'asset',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '1.1.04',
                                'name' => 'Inventario',
                                'account_type' => 'asset',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '1.1.05',
                                'name' => 'IVA Crédito Fiscal',
                                'account_type' => 'asset',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                        ],
                    ],
                    [
                        'code' => '1.2',
                        'name' => 'ACTIVO NO CORRIENTE',
                        'account_type' => 'asset',
                        'nature' => 'debit',
                        'level' => 2,
                        'is_detail' => false,
                        'children' => [
                            [
                                'code' => '1.2.01',
                                'name' => 'Muebles y Útiles',
                                'account_type' => 'asset',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '1.2.02',
                                'name' => 'Equipos de Computación',
                                'account_type' => 'asset',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                        ],
                    ],
                ],
            ],

            // 2. PASIVO
            [
                'code' => '2',
                'name' => 'PASIVO',
                'account_type' => 'liability',
                'nature' => 'credit',
                'level' => 1,
                'is_detail' => false,
                'children' => [
                    [
                        'code' => '2.1',
                        'name' => 'PASIVO CORRIENTE',
                        'account_type' => 'liability',
                        'nature' => 'credit',
                        'level' => 2,
                        'is_detail' => false,
                        'children' => [
                            [
                                'code' => '2.1.01',
                                'name' => 'Cuentas por Pagar',
                                'account_type' => 'liability',
                                'nature' => 'credit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '2.1.02',
                                'name' => 'IVA Débito Fiscal',
                                'account_type' => 'liability',
                                'nature' => 'credit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '2.1.03',
                                'name' => 'Sueldos por Pagar',
                                'account_type' => 'liability',
                                'nature' => 'credit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                        ],
                    ],
                ],
            ],

            // 3. PATRIMONIO
            [
                'code' => '3',
                'name' => 'PATRIMONIO',
                'account_type' => 'equity',
                'nature' => 'credit',
                'level' => 1,
                'is_detail' => false,
                'children' => [
                    [
                        'code' => '3.1',
                        'name' => 'Capital',
                        'account_type' => 'equity',
                        'nature' => 'credit',
                        'level' => 2,
                        'is_detail' => true,
                    ],
                    [
                        'code' => '3.2',
                        'name' => 'Resultados Acumulados',
                        'account_type' => 'equity',
                        'nature' => 'credit',
                        'level' => 2,
                        'is_detail' => true,
                    ],
                    [
                        'code' => '3.3',
                        'name' => 'Resultado del Ejercicio',
                        'account_type' => 'equity',
                        'nature' => 'credit',
                        'level' => 2,
                        'is_detail' => true,
                    ],
                ],
            ],

            // 4. INGRESOS
            [
                'code' => '4',
                'name' => 'INGRESOS',
                'account_type' => 'income',
                'nature' => 'credit',
                'level' => 1,
                'is_detail' => false,
                'children' => [
                    [
                        'code' => '4.1',
                        'name' => 'Ventas',
                        'account_type' => 'income',
                        'nature' => 'credit',
                        'level' => 2,
                        'is_detail' => true,
                    ],
                    [
                        'code' => '4.2',
                        'name' => 'Otros Ingresos',
                        'account_type' => 'income',
                        'nature' => 'credit',
                        'level' => 2,
                        'is_detail' => true,
                    ],
                ],
            ],

            // 5. GASTOS
            [
                'code' => '5',
                'name' => 'GASTOS',
                'account_type' => 'expense',
                'nature' => 'debit',
                'level' => 1,
                'is_detail' => false,
                'children' => [
                    [
                        'code' => '5.1',
                        'name' => 'Costo de Ventas',
                        'account_type' => 'expense',
                        'nature' => 'debit',
                        'level' => 2,
                        'is_detail' => true,
                    ],
                    [
                        'code' => '5.2',
                        'name' => 'Gastos de Administración',
                        'account_type' => 'expense',
                        'nature' => 'debit',
                        'level' => 2,
                        'is_detail' => false,
                        'children' => [
                            [
                                'code' => '5.2.01',
                                'name' => 'Sueldos y Salarios',
                                'account_type' => 'expense',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '5.2.02',
                                'name' => 'Alquileres',
                                'account_type' => 'expense',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '5.2.03',
                                'name' => 'Servicios Básicos',
                                'account_type' => 'expense',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                            [
                                'code' => '5.2.04',
                                'name' => 'Útiles y Materiales',
                                'account_type' => 'expense',
                                'nature' => 'debit',
                                'level' => 3,
                                'is_detail' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->createAccounts($accounts, $tenantId);
    }

    private function createAccounts(array $accounts, int $tenantId, ?int $parentId = null): void
    {
        foreach ($accounts as $accountData) {
            $children = $accountData['children'] ?? [];
            unset($accountData['children']);

            $account = AccountChart::create(array_merge($accountData, [
                'tenant_id' => $tenantId,
                'parent_id' => $parentId,
                'is_active' => true,
                'opening_balance' => 0,
                'current_balance' => 0,
            ]));

            if (!empty($children)) {
                $this->createAccounts($children, $tenantId, $account->id);
            }
        }
    }
}
