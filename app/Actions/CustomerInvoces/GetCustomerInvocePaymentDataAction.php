<?php

namespace App\Actions\CustomerInvoces;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use App\Models\IPTVConfig;
use App\Services\Invoces\InvoiceCalculator;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCustomerInvocePaymentDataAction
{
    use AsAction;

    public function __construct(private readonly InvoiceCalculator $invoiceCalculator) {}

    /** @return array<string, mixed> */
    public function handle(Customer $customer, CustomerInvoce $customerInvoce): array
    {
        abort_unless($customerInvoce->iptv_customer_id === $customer->getKey(), 404);

        return [
            'invoce' => $customerInvoce,
            'GatewaysList' => class_exists(IPTVGateway::class)
                ? IPTVGateway::where('active', 1)->get()
                : [],
            'ConfigData' => IPTVConfig::getAllStringSettings(),
            ...$this->invoiceCalculator->calculate($customer),
        ];
    }
}
