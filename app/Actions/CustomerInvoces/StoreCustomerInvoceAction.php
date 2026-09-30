<?php

namespace App\Actions\CustomerInvoces;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomerInvoceAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Customer $customer, array $data): CustomerInvoce
    {
        return CustomerInvoce::create([
            'iptv_customer_id' => $customer->getKey(),
            'duedate_at' => $data['duedate_at'],
        ]);
    }
}
