<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['api_key'], // API key storing in password field
            'type' => User::TYPE_MERCHANT,   // Set user type to "Merchant"
        ]);

        $merchant = Merchant::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'domain' => $data['domain'],
        ]);

        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = $data['api_key'];  // Update password with new API key
        $user->save();

        $user->merchant->name = $data['name'];
        $user->merchant->domain = $data['domain'];
        $user->merchant->save();
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {

        // TODO: Complete this method
        $user = User::where('email', $email)->first();

        if (!$user) {
            return null; // Return null if user is not found
        }

        return $user->merchant; // Return merchant


    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method

        $orders = Order::where('affiliate_id', $affiliate->id)
            ->where('paid', false)
            ->get();

        foreach ($orders as $order) {
            dispatch(new PayoutOrderJob($order)); // Dispatch a payout job for each order
        }
    }
}
