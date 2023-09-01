<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Devfaysal\BangladeshGeocode\Models\District;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $districtNames = District::where('division_id', 6)->pluck('name');
        
        foreach ($districtNames as $district) {
            echo "Inserting data for district: {$district}\n";
            DB::table('locations')->insert([
                'location_name' => $district,
            ]);
        }
    }
}
