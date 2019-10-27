<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * LanguagesTableSeeder Class add default values to languages table
 * @author Eslam Hassan <e.hassan@shiftebusiness.com>
 */
class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('languages')->insert(
            [
                [
                    'code'        => 'en',
                    'name'        => 'English',
                    'native-name' => 'English',
                    'status'      => 1,
                ],
                [
                    'code'        => 'ar',
                    'name'        => 'Arabic',
                    'native-name' => 'العربية',
                    'status'      => 1,
                ],
                [
                    'code'        => 'fr',
                    'name'        => 'French',
                    'native-name' => 'Français',
                    'status'      => 1,
                ],
                [
                    'code'        => 'de',
                    'name'        => 'German',
                    'native-name' => 'Deutsch',
                    'status'      => 1,
                ],
                [
                    'code'        => 'ru',
                    'name'        => 'Russian',
                    'native-name' => 'Русский',
                    'status'      => 1,
                ],
            ]
        );
    }
}