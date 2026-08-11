<?php

namespace App\Actions\CustomerInvoces;

use App\Models\CustomerInvoce;
use Lorisleiva\Actions\Concerns\AsAction;

class CancelCustomerInvoceAction
{
    use AsAction;

    public function handle(CustomerInvoce $invoce): CustomerInvoce
    {
        $invoce->canceled_at = now();
        $invoce->save();

        return $invoce;
    }
}
