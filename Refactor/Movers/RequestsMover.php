<?php

namespace App\Console\Commands\Refactor\Movers;

use Illuminate\Support\Str;

class RequestsMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $this->moveBaseRequestClasses();
        $this->moveRequestClasses($this->mappings->Models, $this->mappings->Requests);

        $this->createGitKeep( app_path("Http/Requests") );
    }


    public function moveBaseRequestClasses()
    {
        $list = [
            app_path('Http/Requests/FormRequestBase.php') => module_path('Core', 'Http/Requests/FormRequestBase.php'),
            app_path('Http/Requests/RouteKeyExistsRule.php') => module_path('Core', 'Http/Requests/RouteKeyExistsRule.php'),
        ];

        foreach ( $list as $from => $to ) {
            $this->runPhpactorClassMove($from, $to);
        }
    }


    public function moveRequestClasses($mappings_models, $mappings_requests)
    {
        $reverse_map = $this->reverseMergedMaps($mappings_models, $mappings_requests);
        $folders = $this->getFoldersList( app_path("Http/Requests") );

        foreach ( $folders as $folder ) {
            $folder_basename = basename($folder);
            $module = $reverse_map[ Str::singular($folder_basename) ];

            $files = $this->getFilesList($folder);
            foreach ( $files as $file ) {
                $file_basename = basename($file);

                $from = $file;
                $to = module_path($module, "Http/Requests/{$folder_basename}/{$file_basename}");
                $this->runPhpactorClassMove($from, $to);
            }

            $this->removeFolder($folder);
        }
    }
}
