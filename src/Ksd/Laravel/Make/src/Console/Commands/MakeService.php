<?php

namespace Ksd\Laravel\Make\Console\Commands;

use Ksd\Laravel\Make\Console\Commands\MakeBase;

class MakeService extends MakeBase {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model service class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Service';

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        return $this->build($name);
    }
}
