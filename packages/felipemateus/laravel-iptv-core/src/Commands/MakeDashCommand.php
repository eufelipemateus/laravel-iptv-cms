<?php

namespace FelipeMateus\IPTVCore\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeDashCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dash {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create your dash class.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'dash';

     /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the dash will be generated'],
        ];
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    public function getDefaultNamespace($rootnamespace){
        return $rootnamespace . "\Dashs";
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub():string
    {
        return (__DIR__.'/../resources/stubs/DashStub.php');
    }
}
