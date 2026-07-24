<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomerAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Customer
    {
        $data['hash_acess'] = Str::random(64);

        return Customer::create($data);
    }
}
