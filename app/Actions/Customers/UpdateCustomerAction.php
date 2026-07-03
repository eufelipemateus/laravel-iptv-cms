<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCustomerAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Customer $customer, array $data, bool $isActive, bool $regenerateHash): Customer
    {
        if ($regenerateHash) {
            $customer->update(['hash_acess' => Str::random(64)]);

            return $customer;
        }

        $data['active'] = $isActive;
        $customer->update($data);

        return $customer;
    }
}
