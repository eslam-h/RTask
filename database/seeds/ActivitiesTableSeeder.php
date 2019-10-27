<?php

use Dev\Infrastructure\Models\ActivityModels\ActivityModel;
use Illuminate\Database\Seeder;

/**
 * ActivitiesTableSeeder Class add testing data to activites table
 * @author Mohamad El-Wakeel <m.elwakeel@shiftebusiness.com>
 */
class ActivitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ActivityModel::class, 100)->create();
    }
}