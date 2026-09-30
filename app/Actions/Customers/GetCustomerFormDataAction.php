<?php

namespace App\Actions\Customers;

use App\Models\ChannelCdn;
use App\Models\Customer;
use App\Models\CustomerPlan;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCustomerFormDataAction
{
    use AsAction;

    /** @return array<string, mixed> */
    public function handle(?Customer $customer = null): array
    {
        $data = [
            'Planslist' => CustomerPlan::activePlanList(),
            'Cdnslist' => ChannelCdn::all(),
        ];

        if (! $customer) {
            return $data;
        }

        return $data + [
            'Customer' => $customer,
            'PlansAdditionallist' => $customer->planAditionalList(),
            'CustomerPlansAddionalList' => $customer->plans_additional()->get(),
            'CustomerInvoceList' => $customer->customer_invoce()->get(),
            'GatewaysList' => class_exists(IPTVGateway::class)
                ? IPTVGateway::where('active', 1)->get()
                : [],
        ];
    }
}
