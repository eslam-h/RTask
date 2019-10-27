<?php

use Dev\Infrastructure\Models\TagModels\TagModel;
use Dev\Infrastructure\Models\TagModels\TagTranslationModel;
use Illuminate\Database\Seeder;

/**
 * TagsTableSeeder Class
 */
class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        factory(TagModel::class, 10)->create()
			->each(function ($tag) {
				$tag->translations()->save(factory(TagTranslationModel::class)->make());
			});
    }
}
