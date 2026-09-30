<?php

namespace App\Actions\CustomerPlans;

use App\Models\CustomerPlan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListCustomerPlansAction
{
    use AsAction;

    public function handle(int $perPage = 25): LengthAwarePaginator
    {
        return CustomerPlan::query()
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
