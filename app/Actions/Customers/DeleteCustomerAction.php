<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCustomerAction
{
    use AsAction;

    public function handle(Customer $customer): void
    {
        $customer->delete();
    }
}
