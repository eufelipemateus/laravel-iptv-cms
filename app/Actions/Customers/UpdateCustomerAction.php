<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCustomerAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        Customer $customer,
        array $data,
        bool $isActive,
        bool $regenerateToken,
        bool $revokeToken,
    ): Customer
    {
        if ($revokeToken) {
            $customer->revokeAuthToken();

            return $customer;
        }

        $expiresAt = null;
        if (isset($data['auth_token_expires_at']) && filled($data['auth_token_expires_at'])) {
            $expiresAt = Carbon::parse((string) $data['auth_token_expires_at']);
        }

        if ($regenerateToken) {
            $customer->setRelation('plainAuthToken', $customer->issueAuthToken($expiresAt));

            return $customer;
        }

        if (array_key_exists('auth_token_expires_at', $data)) {
            $data['auth_token_expires_at'] = $expiresAt;
        }

        $data['active'] = $isActive;
        $customer->update($data);

        return $customer;
    }
}
