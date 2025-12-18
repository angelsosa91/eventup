<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            [
                'name' => 'Banco Itaú Paraguay S.A.',
                'short_name' => 'Itaú',
                'code' => '0017',
                'swift_code' => 'ITAUPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Banco Continental S.A.E.C.A.',
                'short_name' => 'Continental',
                'code' => '0054',
                'swift_code' => 'CONLPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Banco Regional S.A.E.C.A.',
                'short_name' => 'Regional',
                'code' => '0057',
                'swift_code' => 'BREGPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Banco Nacional de Fomento',
                'short_name' => 'BNF',
                'code' => '0011',
                'swift_code' => 'BNFOPYPX',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Banco GNB Paraguay S.A.',
                'short_name' => 'GNB',
                'code' => '0072',
                'swift_code' => 'GNBAPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Banco Familiar S.A.E.C.A.',
                'short_name' => 'Familiar',
                'code' => '0064',
                'swift_code' => 'BFAMPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Banco BASA S.A.',
                'short_name' => 'BASA',
                'code' => '0094',
                'swift_code' => 'BASAPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Banco Atlas S.A.',
                'short_name' => 'Atlas',
                'code' => '0075',
                'swift_code' => 'ATLAPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Bancop S.A.',
                'short_name' => 'Bancop',
                'code' => '0084',
                'swift_code' => 'BCOPPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Banco Río S.A.E.C.A.',
                'short_name' => 'Banco Río',
                'code' => '0081',
                'swift_code' => 'BSUDPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Sudameris Bank S.A.E.C.A.',
                'short_name' => 'Sudameris',
                'code' => '0086',
                'swift_code' => 'SUDAPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
            [
                'name' => 'Vision Banco S.A.E.C.A.',
                'short_name' => 'Vision',
                'code' => '0088',
                'swift_code' => 'VISBPYPA',
                'country' => 'Paraguay',
                'is_active' => true,
            ],
        ];

        foreach ($banks as $bank) {
            Bank::updateOrCreate(
                ['code' => $bank['code']],
                $bank
            );
        }
    }
}
