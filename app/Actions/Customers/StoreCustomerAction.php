<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomerAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Customer
    {
        $expiresAt = null;
        if (isset($data['auth_token_expires_at']) && filled($data['auth_token_expires_at'])) {
            $expiresAt = Carbon::parse((string) $data['auth_token_expires_at']);
        }

        unset($data['auth_token_expires_at']);

        $customer = Customer::create($data);
        $customer->setRelation('plainAuthToken', $customer->issueAuthToken($expiresAt));

        return $customer;
    }
}
