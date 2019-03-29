<?php

namespace Ksd\Laravel\Make\Console\Commands;

use Ksd\Laravel\Make\Console\Commands\MakeBase;

class MakeModel extends MakeBase {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:mymodel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

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
