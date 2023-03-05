<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Carbon;
use App\Exceptions\PayoutException;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // TODO: Complete this method

            // Calculate payout amount based on the order's subtotal price and merchant  commission rate;
        $amount = $this->order->subtotal_price * $this->order->merchant->commission_rate;

        try {

              // Send payout using API service
            $apiService->sendPayout($this->order->customer_email, $amount);

             // Update order status to paid if payout is successful

            $this->order->update(['status' => Order::PAID]);
        } catch (PayoutException $e) {

            // Log payout failure to payout_logs table if there's an exception\
           
            DB::table('payout_logs')->insert([
                'order_id' => $this->order->id,
                'customer_email' => $this->order->customer_email,
                'amount' => $amount,
                'error_message' => $e->getMessage(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
    
}
