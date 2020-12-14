<?php

namespace App\Console\Commands\Refactor\Cleaners;

class ModuleCleanerBefore
{
    public $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function run()
    {
        $this->removeDefaultController();
        $this->emptyRoutes();
    }

    public function removeDefaultController()
    {
        $path = module_path($this->module, "Http/Controllers/{$this->module}Controller.php");
        if ( is_file($path) ) {
            unlink($path);
        }
    }

    public function emptyRoutes()
    {
        $stub = file_get_contents(__DIR__ . '/../stubs/api.php.stub');
        $dest = module_path($this->module, "Routes/api.php");
        file_put_contents($dest, $stub);

        $stub = file_get_contents(__DIR__ . '/../stubs/web.php.stub');
        $dest = module_path($this->module, "Routes/web.php");
        file_put_contents($dest, $stub);
    }
}
