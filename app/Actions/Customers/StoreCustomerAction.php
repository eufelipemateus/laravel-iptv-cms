<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomerAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Customer
    {
        $data['hash_acess'] = md5((string) now());

        return Customer::create($data);
    }
}
