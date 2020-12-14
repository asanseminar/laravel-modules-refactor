<?php

namespace App\Console\Commands\Refactor\Movers;

use Illuminate\Support\Str;

class SeedersMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $mappings = $this->mergeMaps($this->mappings->Models, $this->mappings->Database->Seeders);
        $this->moveBaseSeeders();
        $this->moveSeedersClasses($mappings);

        $this->createGitKeep( database_path("seeders") );
    }


    public function moveBaseSeeders()
    {
        $this->runPhpactorClassMove( database_path("seeders/SeederBase.php"), module_path("Core", "Database/Seeders/SeederBase.php") );
        $this->runPhpactorClassMove( database_path("seeders/DatabaseSeeder.php"), module_path("Core", "Database/Seeders/DatabaseSeeder.php") );
    }


    public function moveSeedersClasses($mappings)
    {
        $dir = database_path("seeders");
        if ( is_dir($dir) == false ) return;

        $files = $this->getFilesList( $dir );
        $reverse_map = $this->reverseMap($mappings);

        foreach ( $files as $file ) {
            $file_basename = basename($file);
            $resource_base = $this->getSeederBase($file_basename);
            $module = $reverse_map[$resource_base];

            $from = $file;
            $to = module_path($module, "Database/Seeders/{$file_basename}");
            $this->runPhpactorClassMove($from, $to);
        }
    }


    private function getSeederBase($name)
    {
        if ( Str::contains($name, "TableSeeder.php") ) {
            return Str::substr($name, 0, strlen($name)-strlen("TableSeeder.php"));
        }

        return Str::substr($name, 0, strlen($name)-strlen("Seeder.php"));
    }
}
