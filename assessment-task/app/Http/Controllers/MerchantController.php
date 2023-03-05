<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;


class MerchantController extends Controller
{
    public function __construct(MerchantService $merchantService) {

        $this->merchantService = $merchantService;
    }

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method

        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $merchant = $this->merchantService->getCurrentMerchant();

        $fromDate = Carbon::parse($request->input('from_date'));
        $toDate = Carbon::parse($request->input('to_date'));

        $orders = Order::where('merchant_id', $merchant->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $orderCount = $orders->count();
        $commissionOwed = $orders->filter(function ($order) {
            return $order->affiliate_id !== null && !$order->commission_paid;
        })->sum('commission_amount');
        $revenue = $orders->sum('subtotal');

        return response()->json([
            'count' => $orderCount,
            'commission_owed' => $commissionOwed,
            'revenue' => $revenue,
        ]);
    
    }
}
