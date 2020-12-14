<?php

namespace App\Console\Commands\Refactor\Movers;

use Illuminate\Support\Str;

class FactoriesMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $mappings = $this->mergeMaps($this->mappings->Models, $this->mappings->Database->Factories);
        $this->moveFactoriesClasses($mappings);

        $this->createGitKeep( database_path("factories") );
    }


    public function moveFactoriesClasses($mappings)
    {
        $dir = database_path("factories");
        if ( is_dir($dir) == false ) return;

        $files = $this->getFilesList( $dir );
        $reverse_map = $this->reverseMap($mappings);

        foreach ( $files as $file ) {
            $file_basename = basename($file);
            $resource_base = $this->getFactoryBase($file_basename);
            $module = $reverse_map[$resource_base];

            $from = $file;
            $to = module_path($module, "Database/Factories/{$file_basename}");
            $this->runPhpactorClassMove($from, $to);
        }
    }


    private function getFactoryBase($name)
    {
        return Str::substr($name, 0, strlen($name)-strlen("Factory.php"));
    }
}
