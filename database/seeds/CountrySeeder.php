<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for($i=1;$i<=200;$i++)
        {
         $country = new Country();
        $country->name = $faker->country;
        $country->save();
        }
        
    }
    
}
