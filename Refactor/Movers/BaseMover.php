<?php

namespace App\Console\Commands\Refactor\Movers;

use Illuminate\Console\Command;

class BaseMover
{
    /** @var Command $command */
    protected $command;

    public function __construct($command)
    {
        $this->command = $command;
    }


    public function mergeMaps($models_mappings, $other_mappings)
    {
        $mappings = [];
        foreach ( $models_mappings as $module => $models )
        {
            if ( property_exists($other_mappings, $module) ) {
                $models = array_merge($models, $other_mappings->{$module});
            }
            $mappings[$module] = $models;
        }

        return $mappings;
    }


    public function reverseMap($models_mappings)
    {
        $reverse_map = [];
        foreach ( $models_mappings as $module => $models )
            foreach ( $models as $model )
                $reverse_map[$model] = $module;

        return $reverse_map;
    }


    public function reverseMergedMaps($map1, $map2)
    {
        return $this->reverseMap($this->mergeMaps($map1, $map2));
    }


    public function getFilesList($dir)
    {
        $list = array_diff(scandir($dir), array('..', '.'));
        $files = [];

        foreach( $list as $item ) {
            $path = $dir . '/' . $item;
            if ( is_file($path) == false ) continue;
            $files[] = $path;
        }

        return $files;
    }

    public function getFoldersList($dir)
    {
        $list = array_diff(scandir($dir), array('..', '.'));
        $dirs = [];

        foreach( $list as $item ) {
            $path = $dir . '/' . $item;
            if ( is_dir($path) == false ) continue;
            $dirs[] = $path;
        }

        return $dirs;
    }


    public function getFilesListRecursive($dir)
    {
        $files = $this->getFilesList($dir);

        $dirs = $this->getFoldersList($dir);
        foreach ( $dirs as $d ) {
            $files = array_merge($files, $this->getFilesListRecursive($d));
        }

        return $files;
    }


    public function removeFolder($dir)
    {
        if ( is_dir($dir) ) {
            rmdir($dir);
        }
    }


    public function removeFolderRecursive($dir)
    {
        if ( is_dir($dir) == false ) return;

        $dirs = $this->getFoldersList($dir);
        foreach ( $dirs as $d ) {
            $this->removeFolderRecursive($d);
        }

        rmdir($dir);
    }


    public function moveFile($src, $dest)
    {
        if ( is_file($src) ) {
            rename($src, $dest);
        }
    }


    public function backslash4(array $array)
    {
        return join("\\\\", $array);
    }


    public function runPhpactorClassMove($src, $dest)
    {
        if ( file_exists($src) == false ) return;

        $command = "phpactor class:move {$src} {$dest}";
        $output = []; $return_var = 0;
        exec($command, $output, $return_var);

        $this->command->newLine();
        $this->command->info($command);
        foreach ( $output as $line ) $this->command->info($line);
        $this->command->info($return_var);
    }


    public function replaceWords($from, $to, $paths=null)
    {
        $this->command->info("Change word: $from => $to");

        if ( is_null($paths) ) {
            $paths = join(" ", [
                base_path('app'),
                base_path('config'),
                base_path('database'),
                base_path('resources/views'),
                base_path('routes'),
            ]);
        }
        else if ( is_array($paths) ) {
            $paths = join(" ", $paths);
        }

        $command = "find {$paths} -type f -name '*.php' -exec sed -i 's/{$from}/{$to}/g' {} +";
        $output = []; $return_var = 0;
        exec($command, $output, $return_var);

        $this->command->newLine();
        $this->command->info($command);
        // foreach ( $output as $line ) $this->info($line);
        $this->command->info($return_var);
    }


    public function createGitKeep($dir)
    {
        file_put_contents("{$dir}/.gitkeep", "");
    }
}
