<?php

namespace Ksd\Laravel\Make\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MakeMRS extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:mrs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model / repository / service class';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "make:mrs {name} {--subnamespace=}";

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
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['subnamespace', null, InputOption::VALUE_OPTIONAL, 'sub namespace option.', null]
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');
        if (!$name) return $this->error('Not enough arguments (missing: "name").');

        $subnamespace = $this->option('subnamespace');

        $this->call('make:mymodel', [
            'name' => $name, '--subnamespace' => $subnamespace
        ]);

        $this->call('make:repository', [
            'name' => $name, '--subnamespace' => $subnamespace
        ]);

        $this->call('make:service', [
            'name' => $name, '--subnamespace' => $subnamespace
        ]);

        $this->info("{$name} Model/Repository/Service created successfully.");
    }
}
