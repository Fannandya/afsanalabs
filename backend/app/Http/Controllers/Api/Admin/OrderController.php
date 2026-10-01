<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $limit = min(max((int) $request->integer('limit', 20), 1), 100);
        $paginator = Order::query()->orderByDesc('created_at')->orderByDesc('id')->paginate($limit);

        return response()->json([
            'data' => $paginator->items(),
            'page' => $paginator->currentPage(),
            'limit' => $paginator->perPage(),
        ]);
    }

    public function show(string $id)
    {
        return response()->json(['data' => Order::findOrFail($id)]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, string $id)
    {
        $row = Order::findOrFail($id);
        $row->update(['status' => $request->validated()['status']]);

        return response()->json(['data' => $row->fresh()]);
    }
}
