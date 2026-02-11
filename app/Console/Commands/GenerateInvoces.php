<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\Customer;
use App\Models\CustomerInvoce;

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

        $this->info( sprintf('Generating invoices for the month %s.', date('m/Y')) );
        $customers = Customer::where("active",1)->get();

        foreach($customers as $customer){
            $format = sprintf('Y-m-%d', $customer->due_day);
            $due_day_this_month = date($format);
            CustomerInvoce::create(['duedate_at'=>$due_day_this_month,'customer_id'=> $customer->id]);
            $message = sprintf('Generated invoce to %s with due date %s.', $customer->name, $due_day_this_month );
            $this->warn($message);
        }
        $this->info('All Invoces generate successfully.');
    }
}
