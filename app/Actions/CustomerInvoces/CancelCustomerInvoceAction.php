<?php

namespace App\Actions\CustomerInvoces;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use Lorisleiva\Actions\Concerns\AsAction;

class CancelCustomerInvoceAction
{
    use AsAction;

    public function handle(Customer $customer, CustomerInvoce $invoce): CustomerInvoce
    {
        abort_unless($invoce->iptv_customer_id === $customer->getKey(), 404);

        $invoce->canceled_at = now();
        $invoce->save();

        return $invoce;
    }
}
