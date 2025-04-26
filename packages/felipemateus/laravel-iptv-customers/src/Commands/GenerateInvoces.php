<?php

namespace FelipeMateus\IPTVCustomers\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use FelipeMateus\IPTVCustomers\Models\IPTVCustomer;
use FelipeMateus\IPTVCustomers\Models\IPTVCustomerInvoce;

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
        $customers = IPTVCustomer::where("active",1)->get();

        foreach($customers as $customer){
            $format = sprintf('Y-m-%d', $customer->due_day);
            $due_day_this_month = date($format);
            IPTVCustomerInvoce::create(['duedate_at'=>$due_day_this_month,'iptv_customer_id'=> $customer->id]);
            $message = sprintf('Generated invoce to %s with due date %s.', $customer->name, $due_day_this_month );
            $this->warn($message);
        }
        $this->info('All Invoces generate successfully.');
    }
}
