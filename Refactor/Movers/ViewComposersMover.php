<?php

namespace App\Console\Commands\Refactor\Movers;

use Illuminate\Support\Str;

class ViewComposersMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $view_composers_mappings = $this->mappings->ViewComposers;
        $this->moveViewComposerClasses($view_composers_mappings);
        $this->removeFolder( app_path("Http/View/Composers") );
    }


    public function moveViewComposerClasses($mappings)
    {
        foreach ( $mappings as $module => $classes )
        {
            foreach ( $classes as $class )
            {
                $from = app_path("Http/View/Composers/{$class}.php");
                $to = module_path($module, "Http/View/Composers/{$class}.php");
                $this->runPhpactorClassMove($from, $to);
            }
        }
    }
}
