<?php

use Dev\Infrastructure\Models\TripModels\TripCommentModel;
use Illuminate\Database\Seeder;

class TripCommentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(TripCommentModel::class, 100)->create();
    }
}
