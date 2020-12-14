<?php

namespace App\Console\Commands\Refactor\Movers;

use Illuminate\Support\Str;

class MigrationsMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $mappings = $this->mergeMaps($this->mappings->Models, $this->mappings->Database->Migrations);
        $this->moveMigrationsClasses($mappings);

        $this->createGitKeep( database_path("migrations") );
    }


    public function moveMigrationsClasses($mappings)
    {
        $dir = database_path("migrations");
        if ( is_dir($dir) == false ) return;

        $files = $this->getFilesList( $dir );
        $reverse_map = $this->reverseMap($mappings);

        foreach ( $files as $file ) {
            $file_basename = basename($file);
            $resource_base = $this->getMigrationBase($file_basename);
            $module = $reverse_map[$resource_base];

            $from = $file;
            $to = module_path($module, "Database/Migrations/{$file_basename}");
            $this->command->info("\nMove Migration:\n{$from}\n{$to}\n");
            $this->moveFile($from, $to);
        }
    }


    private function getMigrationBase($name)
    {
        return $name;
    }
}
