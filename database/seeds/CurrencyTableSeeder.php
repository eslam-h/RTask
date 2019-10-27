<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class CityTableSeeder
 */
class CurrencyTableSeeder extends seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->insert(
            [
                [
                    'currency' => 'EGP',
                    'code'     => 'EGP',
                ],
                [
                    'currency' => 'USD',
                    'code'     => 'USD',
                ],
                [
                    'currency' => 'EUR',
                    'code'     => 'EUR',
                ],
                [
                    'currency' => 'CNY',
                    'code'     => 'CNY',
                ],
                [
                    'currency' => 'AUD',
                    'code'     => 'AUD',
                ],
                [
                    'currency' => 'AED',
                    'code'     => 'AED',
                ],
            ]
        );
    }
}