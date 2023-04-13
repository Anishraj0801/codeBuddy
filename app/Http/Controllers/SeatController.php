<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SeatController extends Controller
{
    public function book(Request $request)
{
    $num_tickets = $request->input('num_tickets', 1);
    $seat_number = $request->input('seat_number');

    // Validate input
    $validator = Validator::make($request->all(), [
        'num_tickets' => 'required|integer|min:1|max:5',
        'seat_number' => [
            'required',
            'regex:/^[A-T][1-9]\d*$/',
            function ($attribute, $value, $fail) {
                $seat = Seat::where('seat_number', $value)->first();
                if (!$seat) {
                    return $fail('Invalid seat number entered. Please enter a seat between A1 and T10.');
                }
                if ($seat->is_booked) {
                    return $fail("The requested seat $value is already booked. Please select another seat.");
                }
            }
        ],
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 400);
    }

    // Find available adjacent seats
    $start = ord(substr($seat_number, 0, 1));
    $end = $start + 19;
    $seats = Seat::whereBetween('seat_number', [chr($start), chr($end) . '999'])
                 ->where('is_booked', false)
                 ->limit($num_tickets)
                 ->get();

    if (count($seats) < $num_tickets) {
        $suggested_seats = Seat::whereBetween('seat_number', [chr($start), chr($end) . '999'])
                                ->where('is_booked', false)
                                ->limit($num_tickets)
                                ->pluck('seat_number')
                                ->toArray();
        return response()->json(['message' => "Sorry, there are not enough adjacent seats available. Please try again with a smaller number of tickets.", 'suggested_seats' => $suggested_seats], 400);
    }

    // Book the seats
    $seat_list = [];
    foreach ($seats as $seat) {
        $seat->is_booked = true;
        $seat->save();
        $seat_list[] = $seat->seat_number;
    }
    return response()->json(['message' => "Seats " . implode(', ', $seat_list) . " have been booked successfully."]);
}

}
    
