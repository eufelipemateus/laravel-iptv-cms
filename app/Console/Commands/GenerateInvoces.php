<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class GenerateInvoces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoce:month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate All invoces to current month.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! config('modules.customer.enabled', false)) {
            $this->error('Customer is disabled!!');
            exit;
        }
        $now = Date::now();
        $this->info(sprintf('Generating invoices for the month %s.', $now->format('m/Y')));
        $customers = Customer::where('active', 1)->get();

        foreach ($customers as $customer) {
            $day = min((int) $customer->due_day, $now->daysInMonth);
            $due_day_this_month = $now->setDay($day)->toDateString();

            CustomerInvoce::firstOrCreate([
                'iptv_customer_id' => $customer->id,
                'duedate_at' => $due_day_this_month,
            ]);

            $message = sprintf('Generated invoce to %s with due date %s.', $customer->name, $due_day_this_month);
            $this->warn($message);
        }
        $this->info('All Invoces generate successfully.');
    }
}
