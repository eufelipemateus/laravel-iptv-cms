<?php

namespace App\Actions\Customers;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListCustomersAction
{
    use AsAction;

    public function handle(int $perPage = 25): LengthAwarePaginator
    {
        return Customer::query()
            ->with('plan')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
