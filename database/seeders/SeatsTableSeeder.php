<?php

namespace Database\Seeders;

use App\Models\Seat;
use Illuminate\Database\Seeder;

class SeatsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seat_number = 1;
        for ($row = 1; $row <= 10; $row++) {
            for ($col = 1; $col <= 20; $col++) {
                $seat = new Seat;
                $seat->seat_number = chr($col + 64) . $row;
                $seat->is_booked = false;
                $seat->save();
                $seat_number++;
            }
        }
    }
}