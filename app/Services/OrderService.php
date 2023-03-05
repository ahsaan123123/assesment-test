<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
         
         // Check if the order_id already exists in the database
    $existingOrder = Order::where('order_id', $data['order_id'])->first();
        if ($existingOrder) {
            return;
        }

        // Check if an affiliate already exists with the given email, if not, create a new affiliate

    $affiliate = Affiliate::where('email', $data['customer_email'])->first();
    
    if (!$affiliate) {
       
          // Get the merchant with the given domain

        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
        if (!$merchant) {
            throw new \InvalidArgumentException('Invalid merchant domain');
        }
        $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);
    }

    // Register a new affiliate with the given merchant, email, name, and commission rate
    
    $order = new Order([
        'order_id' => $data['order_id'],
        'subtotal_price' => $data['subtotal_price'],
        'discount_code' => $data['discount_code'],
    ]);
       
     // Associate the order with the affiliate and merchant

    $order->affiliate()->associate($affiliate);
    $order->merchant()->associate($merchant);

        //saving of order ;
    $order->save();


    }
}