<?php

namespace App\Actions\CustomerPlans;

use App\Models\CustomerPlan;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVTaxVat;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCustomerPlanFormDataAction
{
    use AsAction;

    /** @return array<string, mixed> */
    public function handle(?CustomerPlan $customerPlan = null): array
    {
        $data = [
            'TaxVatList' => class_exists(IPTVTaxVat::class)
                ? IPTVTaxVat::where('active', true)->get()
                : [],
        ];

        if ($customerPlan) {
            $data += [
                'Plan' => $customerPlan,
                'GroupList' => $customerPlan->groupsList(),
                'PlanGroupList' => $customerPlan->groups,
            ];
        }

        return $data;
    }
}
