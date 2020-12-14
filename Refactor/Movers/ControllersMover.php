<?php

namespace App\Console\Commands\Refactor\Movers;

class ControllersMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $folders = [
            "Admin" => "Admin",
            "Api" => "Api",
            "App" => "App",
            "Auth" => "Auth",
        ];

        foreach ( $folders as $key => $folder ) {
            $this->moveControllers($this->mappings->Models, $this->mappings->Controllers->{$key}, $folder);
        }

        $this->createGitKeep( app_path("Http/Controllers") );
    }


    public function moveControllers($models_mappings, $base_mappings, $folder)
    {
        $dir = $this->getControllersDirectory($folder);
        if ( is_dir($dir) == false ) return;

        $reverse_map = $this->reverseMergedMaps($models_mappings, $base_mappings);
        $files = $this->getFilesList($dir);
        $this->moveControllerFiles($files, $reverse_map, $folder);

        if ( $folder != "" ) {
            $this->removeFolder($dir);
        }
    }


    public function moveControllerFiles($files, $reverse_map, $folder)
    {
        foreach ( $files as $file ) {
            $from = $file;
            $from_basename = basename($from);

            $controller_base_name = $this->getControllerBaseName($folder, $from_basename);
            $module = $reverse_map[$controller_base_name];
            $controllers_folder = $folder=="" ? "" : "/{$folder}";
            $to = module_path($module, "Http/Controllers{$controllers_folder}/{$from_basename}");

            if ( file_exists( $from ) ) {
                $to_folder = dirname($to);
                if ( file_exists($to_folder) == false ) mkdir($to_folder);

                $this->runPhpactorClassMove($from, $to);
            }
        }
    }


    public function getControllersDirectory($folder="")
    {
        $folder = $folder=="" ? "" : "/{$folder}";
        return app_path("Http/Controllers{$folder}");
    }


    public function getControllerBaseName($prepend, $filename)
    {
        $matches = [];
        preg_match("/{$prepend}(\w*)Controller\.php/", $filename, $matches);
        return $matches[1];
    }
}
