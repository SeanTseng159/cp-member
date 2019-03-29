<?php

namespace Ksd\Laravel\Make\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeBase extends GeneratorCommand
{
    protected $isExtendsStub = true;

    /**
     * Build the class.
     * @param  string  $name
     * @return string
     */
    protected function build($name)
    {
        if (!$this->confirm("Do you use to extends base {$this->type} class?")) {
            $this->isExtendsStub = false;
        }

        $this->setSignature();
        $stub = $this->files->get($this->getStub());
        $className = ucfirst(trim($this->argument('name')));

        return $this->replaceNamespace($stub, $this->getDefaultNamespace())
                        ->replaceClassName($stub, $className)
                        ->replaceClass($stub, $className);
    }

    /**
     * Set signature.
     *
     * @return string
     */
    protected function setSignature($signature = '')
    {
        $type = ucfirst(str_plural(strtolower(trim($this->type))));
        $this->signature = $signature ?: $this->name . " {name} {--subnamespace}";

        return $this;
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
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = ucfirst(trim($this->argument('name')));
        $type = ucfirst(strtolower(trim($this->type)));

        $nameInput = (strtolower(trim($this->type)) !== 'model') ? $name . $type : $name;

        return $nameInput;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
    	$type = strtolower(trim($this->type));
        $type = ($this->isExtendsStub) ? 'extends' . ucfirst($type) : $type;
        return dirname(dirname(dirname(__FILE__))) . '/Stubs/' . $type . '.stub';
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceNamespace(&$stub, $namespace)
    {
        $stub = str_replace(
            '{{namespace}}', $namespace, $stub
        );

        return $this;
    }

    /**
     * Replace the className for the given stub.
     *
     * @param  string  $stub
     * @return $this
     */
    protected function replaceClassName(&$stub, $className)
    {
        $stub = str_replace(
            '{{className}}', $className, $stub
        );

        return $this;
    }

    /**
	* Get the default namespace for the class.
	*
	* @param string $rootNamespace
	* @return string
	*/
	protected function getDefaultNamespace($rootNamespace = 'App')
	{
        $type = ucfirst(str_plural(strtolower(trim($this->type))));
        $subnamespace = $this->option('subnamespace');

        $baseNamespace = $rootNamespace . '\\' . $type;
        if ($subnamespace) $baseNamespace .= '\\' . $subnamespace;

		return $baseNamespace;
	}
}
