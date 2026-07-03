<?php

namespace App\Services\Invoces;

use App\Models\Customer;

class InvoiceCalculator
{
    /**
     * @return array<string, mixed>
     */
    public function calculate(Customer $customer): array
    {
        $services = [];
        $plans = collect([$customer->plan])->merge($customer->plans_additional);

        foreach ($plans as $index => $plan) {
            $price = round((float) $plan->price, 2);
            $discount = 0.0;
            $taxPercent = round((float) ($plan->tax_vat->porcent ?? 0), 2);
            $subtotal = round($price - $discount, 2);
            $tax = round($subtotal * ($taxPercent / 100), 2);

            $services[] = [
                'service' => $plan->name,
                'service_type' => $index === 0 ? 'Principal' : 'Additional',
                'price' => $price,
                'discont' => $discount,
                'tax' => $tax,
                'tax_porcent' => $taxPercent,
                'total' => round($subtotal + $tax, 2),
                'subtotal' => $subtotal,
            ];
        }

        $subtotal = round(array_sum(array_column($services, 'price')), 2);
        $totalDiscount = round(array_sum(array_column($services, 'discont')), 2);
        $total = round($subtotal - $totalDiscount, 2);
        $totalTax = round(array_sum(array_column($services, 'tax')), 2);

        return [
            'services' => $services,
            'subtotal' => $subtotal,
            'totalDiscount' => $totalDiscount,
            'total' => $total,
            'totalTax' => $totalTax,
            'final' => round($total + $totalTax, 2),
        ];
    }
}
