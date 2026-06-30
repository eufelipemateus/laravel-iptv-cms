<?php

namespace App\Actions\Customers;

use App\Models\Customer;
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
            $customer->update(['hash_acess' => md5((string) now())]);

            return $customer;
        }

        $data['active'] = $isActive;
        $customer->update($data);

        return $customer;
    }
}
