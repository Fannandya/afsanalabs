<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderTrackController extends Controller
{
    public function show(string $trackingCode)
    {
        $row = Order::where('tracking_code', $trackingCode)->firstOrFail();

        return response()->json(['data' => [
            'tracking_code' => $row->tracking_code,
            'order_type' => $row->order_type,
            'status' => $row->status,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]]);
    }
}
