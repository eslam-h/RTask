<?php

use Illuminate\Database\Seeder;
use Dev\Infrastructure\Models\TagModels\TagModel;
use Dev\Infrastructure\Models\TripModels\TripModel;
use Faker\Factory as Faker;
    

class TripsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(TripModel::class, 500)->create();

//        $tagIds = TagModel::pluck('id')->toArray();
//    	$faker = Faker::create('App\Trip');
//    	$data = [];
//    	for ($i = 1; $i <= 50; $i++) {
//    		// $filepath = 'storage/cdn/trips/images/'.$i ;
//		    // if(!File::exists($filepath)){
//		    //     File::makeDirectory($filepath, 0777, true);  //follow the declaration to see the complete signature
//		    // }
//    		$data =  [
//							'name'                 => $faker->sentence(),
//							//'photo'				   =>  "trips/$i/" . $faker->image($filepath ,400,300 , null, false),
//							'city-id'              => rand(1,20),
//							'price'                => rand(100,2000),
//							'currency-id'          => 1,
//							'activity-id'          => rand(1,9),
//							'is-published'         => rand(1,0),
//							'confirmation-type'    => 'instant',
//							'time-to-confirm'      => NULL,
//							'time-to-confirm-type' => NULL,
//							'created-at'           => NULL,
//							'created-by'           => NULL,
//							'modified-by'          => NULL,
//							'modified-at'          => NULL,
//							'start-date'           => '2019-06-'.rand(20,30),
//							'start-time'           => rand(8,12).':00:00',
//							'end-date'             => '2019-06-'.rand(20,30),
//							'end-time'             => rand(16,22).':00:00',
//							'is-deleted'           => 0,
//                       ];
//        	$tripId = DB::table('trips')->insertGetId($data);
//
//    		$filepath = 'storage/cdn/trips/images/'.$tripId ;
//		    if(!File::exists($filepath)){
//		        File::makeDirectory($filepath, 0777, true);
//		    }
//		    try {
//		    	$imgList = array(
//		    				'act1.jpg',
//		    				'diving.jpg',
//		    				'Marsa-Alam-2.png',
//		    				'maxresdefault.jpg',
//		    				'sea.jpg',
//		    				'Siwa-Oasis-Tour.jpg',
//		    				'snorkeling.jpg'
//		    			   );
//		    	$randKey = array_rand($imgList);
//		    	$image  = 'trips-images/'.$imgList[$randKey];
//		    	//$image = "trips/images/$tripId/" . $faker->image($filepath ,300,400 , 'nature', false);
//		    } catch (Exception $e) {
//		    	$image = '';
//		    }
//            DB::table('trips')->where('id', $tripId)->update(['photo' => $image]);
//            if($tagIds){
//            	$fakerTag = Faker::create();
//            	$tagIdsCount  = count($tagIds);
//            	$randTagCount = rand(1,$tagIdsCount);
//            	$tagIdsData   = [];
//            	for ($x=1; $x <= $randTagCount; $x++) {
//            		$tagId = $fakerTag->unique()->randomElement($tagIds);
//            		$tagIdsData[] = ['tag-id' => $tagId , 'trip-id' => $tripId];
//            	}
//            	DB::table('trip-tags')->insert($tagIdsData);
//            }
//    	}
    }
}