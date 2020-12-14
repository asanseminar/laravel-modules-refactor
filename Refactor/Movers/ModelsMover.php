<?php

namespace App\Console\Commands\Refactor\Movers;

class ModelsMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        // CAVEAT! Please read README.md about moving User model

        $this->addMissingUseStatementsInModels($this->mappings->Models);
        $this->moveModels($this->mappings->Models);

        $this->createUserClassInAppModels();
        $this->fixAuthConfigFile();
        $this->addMorphMapForUserClassInAppServiceProvider();

        $this->createGitKeep( app_path("Models") );
    }


    public function addMissingUseStatementsInModels($models_map)
    {
        $all_models = [];
        foreach ( $models_map as $module => $models )
            foreach ($models as $model)
                $all_models[] = $model;

        foreach ( $all_models as $model1 )
        {
            $path = app_path("Models/{$model1}.php");
            if ( file_exists($path) == false ) continue;

            $content = file_get_contents($path);

            $found_models = [];
            foreach ( $all_models as $model2 )
                if ( ($model2 != $model1) && (str_contains($content, "$model2::") || str_contains($content, "$model2\n")) )
                    $found_models[] = $model2;


            $find = "namespace App\Models;";
            $use_pos = strpos($content, $find) + strlen($find) + 1;
            $before = substr($content, 0, $use_pos);
            $after = substr($content, $use_pos);
            $write = "\n";
            foreach ( $found_models as $model ) {
                $new_use = "use App\\Models\\{$model};";
                if ( str_contains($content, $new_use) == false ) {
                    $write .= "{$new_use}\n";
                }
            }
            $new_content = $before . $write . $after;
            file_put_contents($path, $new_content);
        }
    }


    public function moveModels($models_map)
    {
        foreach ( $models_map as $module => $models )
        {
            foreach ( $models as $model )
            {
                $model_path = app_path("Models/{$model}.php");
                if ( file_exists($model_path) == false ) continue;
                $move_path = module_path($module, "Entities/{$model}.php");
                $this->runPhpactorClassMove($model_path, $move_path);

                $model_namespace = $this->backslash4(["App", "Models", $model]);
                $move_namespace = $this->backslash4(["Modules", $module, "Entities", $model]);
                $this->replaceWords($model_namespace, $move_namespace);

                $this->replaceWords("use $move_namespace;", "", $move_path);
            }
        }
    }


    public function createUserClassInAppModels()
    {
        $stub = file_get_contents(__DIR__ . '/../stubs/User.php.stub');
        $dest = app_path('Models/User.php');
        file_put_contents($dest, $stub);
    }


    public function fixAuthConfigFile()
    {
        $from = $this->backslash4(["User", "Entities", "User::class"]);
        $to = $this->backslash4(["App", "Models", "User::class"]);
        $this->replaceWords($from, $to, config_path('auth.php'));
    }


    public function addMorphMapForUserClassInAppServiceProvider()
    {
        $stub = file_get_contents(__DIR__ . '/../stubs/AppServiceProvider.php.stub');
        $dest = app_path('Providers/AppServiceProvider.php');
        file_put_contents($dest, $stub);
    }
}
