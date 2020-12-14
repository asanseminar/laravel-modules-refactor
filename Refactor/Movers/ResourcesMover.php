<?php

namespace App\Console\Commands\Refactor\Movers;

use Illuminate\Support\Str;

class ResourcesMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $mappings = $this->mergeMaps($this->mappings->Models, $this->mappings->Resources);
        $this->addMissingUseStatementsInResources($mappings);
        $this->moveResourceClasses($mappings);

        $this->createGitKeep( app_path("Http/Resources") );
    }


    public function addMissingUseStatementsInResources($mappings)
    {
        $all_resources = [];
        foreach ( $mappings as $module => $resources )
            foreach ($resources as $resource) {
                $all_resources[] = $resource;
            }

        foreach ( $all_resources as $resource1 )
        {
            $path = app_path("Http/Resources/{$resource1}Resource.php");
            if ( file_exists($path) == false ) continue;

            $content = file_get_contents($path);

            $found_resources = [];
            foreach ( $all_resources as $resource2 )
                if ( ($resource2 != $resource1) && (str_contains($content, "new {$resource2}Resource")) )
                    $found_resources[] = $resource2;


            $find = "namespace App\Http\Resources;";
            $use_pos = strpos($content, $find) + strlen($find) + 1;
            $before = substr($content, 0, $use_pos);
            $after = substr($content, $use_pos);
            $write = "\n";
            foreach ( $found_resources as $resource ) {
                $new_use = "use App\\Http\\Resources\\{$resource}Resource;";
                if ( str_contains($content, $new_use) == false ) {
                    $write .= "{$new_use}\n";
                }
            }
            $new_content = $before . $write . $after;
            file_put_contents($path, $new_content);
        }
    }


    public function moveResourceClasses($mappings)
    {
        $reverse_map = $this->reverseMap($mappings);
        $files = $this->getFilesList( app_path("Http/Resources") );

        foreach ( $files as $file ) {
            $file_basename = basename($file);
            $resource_base = $this->getResourceBase($file_basename);
            $module = $reverse_map[$resource_base];

            $from = $file;
            $to = module_path($module, "Http/Resources/{$file_basename}");
            $this->runPhpactorClassMove($from, $to);
        }
    }


    private function getResourceBase($name)
    {
        return Str::substr($name, 0, strlen($name)-strlen("Resource.php"));
    }
}
