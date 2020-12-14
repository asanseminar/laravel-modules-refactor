<?php

namespace App\Console\Commands\Refactor\Movers;

class ServicesMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $this->moveServiceBase();

        $folders = [
            "Api" => "Api",
            "Admin" => "Admin",
            "App" => "App",
            "Auth" => "Auth",
        ];

        foreach ( $folders as $key => $value ) {
            $models_mappings = $this->mappings->Models;
            $services_mappings = $this->mappings->Services->{$key};
            if ( is_object($services_mappings) ) {
                $this->moveServices($models_mappings, $services_mappings, $value);
            }
            else if ( is_string($services_mappings) ) {
                $this->moveServicesToModule($services_mappings, $value);
            }
        }

        $this->createGitKeep( app_path("Http/Services") );
    }


    public function moveServiceBase()
    {
        $this->runPhpactorClassMove(app_path("Http/Services/ServiceBase.php"), module_path("Core", "Http/Services/ServiceBase.php"));
    }


    public function moveServices($models_mappings, $services_mappings, $folder)
    {
        $dir = $this->getServicesDirectory($folder);
        if ( is_dir($dir) == false ) return;

        $reverse_map = $this->reverseMergedMaps($models_mappings, $services_mappings);
        $files = $this->getFilesList($dir);
        $this->moveServiceFiles($files, $reverse_map, $folder);

        if ( $folder != "" ) {
            $this->removeFolder($dir);
        }
    }


    public function moveServicesToModule($module, $folder)
    {
        $dir = $this->getServicesDirectory($folder);
        if ( is_dir($dir) == false ) return;

        $files = $this->getFilesListRecursive($dir);
        $this->moveAllServiceFilesToModule($files, $module);

        if ( $folder != "" ) {
            $this->removeFolderRecursive($dir);
        }
    }


    public function moveAllServiceFilesToModule($files, $module)
    {
        foreach ( $files as $file )
        {
            $relative_path = substr($file, strlen(app_path("Http/Services/")));
            $from = app_path("Http/Services/{$relative_path}");
            $to = module_path($module, "Http/Services/{$relative_path}");
            $this->runPhpactorClassMove($from, $to);
        }
    }


    public function moveServiceFiles($files, $reverse_map, $folder)
    {
        foreach ( $files as $file ) {
            $from = $file;
            $from_basename = basename($from);

            $service_base_name = $this->getServiceBaseName($folder, $from_basename);
            $module = $reverse_map[$service_base_name];
            $services_folder = $folder=="" ? "" : "/{$folder}";
            $to = module_path($module, "Http/Services{$services_folder}/{$from_basename}");

            if ( file_exists( $from ) ) {
                $to_folder = dirname($to);
                if ( file_exists($to_folder) == false ) mkdir($to_folder);

                $this->runPhpactorClassMove($from, $to);
            }
        }
    }


    public function getServicesDirectory($folder="")
    {
        $folder = $folder=="" ? "" : "/{$folder}";
        return app_path("Http/Services{$folder}");
    }


    public function getServiceBaseName($prepend, $filename)
    {
        $matches = [];
        preg_match("/{$prepend}(\w*)Service\.php/", $filename, $matches);
        return $matches[1];
    }
}
