<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method

         // Check if an affiliate with the given email already exists for this merchant
        $existingAffiliate = Affiliate::where('email', $email)
        ->where('merchant_id', $merchant->id)
        ->first();

            if ($existingAffiliate) {
                throw new AffiliateCreateException('Affiliate with this email already exists for this merchant');
            }

              // Create a new Affiliate object with the provided data
    
     $affiliate = new Affiliate([
            'email' => $email,
            'name' => $name,
            'commission_rate' => $commissionRate,
        ]);

    
    // Associate the new affiliate with the given merchant

    $affiliate->merchant()->associate($merchant);
    $affiliate->save();

     // Create a new User object with the provided email 
    $user = new User(['email' => $email]);
    Mail::to($user)->send(new AffiliateCreated($affiliate));

    return $affiliate;
    }
}