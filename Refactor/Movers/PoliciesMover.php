<?php

namespace App\Console\Commands\Refactor\Movers;

class PoliciesMover extends BaseMover
{
    private $mappings;
    private $policies_list = [];

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $this->moveBasePolicy();
        $this->moveModelPolicies($this->mappings->Models);
        $this->fixPoliciesListInAuthServiceProvider();
        $this->removeAppPoliciesFolder();
    }


    public function moveBasePolicy()
    {
        $policy_path = app_path("Policies/PolicyBase.php");
        $move_path = module_path("Core", "Policies/PolicyBase.php");
        $this->runPhpactorClassMove($policy_path, $move_path);
    }


    public function moveModelPolicies($models_map)
    {
        foreach ( $models_map as $module => $models )
        {
            if ( $module == "Core" ) continue;

            foreach ( $models as $model )
            {
                $policy_path = app_path("Policies/{$model}Policy.php");
                if ( file_exists($policy_path) ) {
                    $move_path = module_path($module, "Policies/{$model}Policy.php");
                    $this->runPhpactorClassMove($policy_path, $move_path);

                    $this->policies_list[] = [
                        "model" => "\\Modules\\{$module}\\Entities\\{$model}::class",
                        "policy" => "\\Modules\\{$module}\\Policies\\{$model}Policy::class",
                    ];
                }
            }
        }
    }


    public function fixPoliciesListInAuthServiceProvider()
    {
        $stubAuthServiceProvider = file_get_contents(__DIR__ . "/../stubs/AuthServiceProvider.php.stub");

        $policies_code = "";
        foreach ( $this->policies_list as $policy ) {
            $policies_code .= "        {$policy['model']} => {$policy['policy']},\n";
        }
        $content = str_replace('$policies$', $policies_code, $stubAuthServiceProvider);

        file_put_contents(app_path('Providers/AuthServiceProvider.php'), $content);
    }


    public function removeAppPoliciesFolder()
    {
        $this->removeFolder(app_path('Policies'));
    }
}
