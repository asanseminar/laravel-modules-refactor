<?php

namespace App\Console\Commands\Refactor;

use App\Console\Commands\Refactor\Cleaners\ModuleCleanerBefore;
use App\Console\Commands\Refactor\Cleaners\ModuleCleanerAfter;
use App\Console\Commands\Refactor\Movers\ControllersMover;
use App\Console\Commands\Refactor\Movers\FactoriesMover;
use App\Console\Commands\Refactor\Movers\MigrationsMover;
use App\Console\Commands\Refactor\Movers\ModelsMover;
use App\Console\Commands\Refactor\Movers\PoliciesMover;
use App\Console\Commands\Refactor\Movers\RequestsMover;
use App\Console\Commands\Refactor\Movers\ResourcesMover;
use App\Console\Commands\Refactor\Movers\ResponsesMover;
use App\Console\Commands\Refactor\Movers\SeedersMover;
use App\Console\Commands\Refactor\Movers\ServicesMover;
use App\Console\Commands\Refactor\Movers\ViewComposersMover;
use Illuminate\Console\Command;

class ModulesMoveClasses extends Command
{
    protected $signature = 'command:modules_move_classes';
    protected $description = 'Modules Move Classes';

    private $map;
    
    private $movers = [
        ModelsMover::class => true,
        PoliciesMover::class => true,
        ControllersMover::class => true,
        ServicesMover::class => true,
        RequestsMover::class => true,
        ResourcesMover::class => true,
        ResponsesMover::class => true,
        ViewComposersMover::class => true,
        FactoriesMover::class => true,
        SeedersMover::class => true,
        MigrationsMover::class => true,
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->map = $this->loadMappings();

        $this->createModules();
        $this->cleanModulesBefore();
        $this->move();
        $this->cleanModulesAfter();

        return 0;
    }

    public function loadMappings()
    {
        $path = __DIR__ . "/mappings.json";
        $content = json_decode( file_get_contents($path) );
        return $content;
    }

    public function createModules()
    {
        foreach ( $this->map->Modules as $module ) {
            $this->info($module);
            $this->call("module:make", ['name' => [$module]]);
        }
    }

    public function move()
    {
        $mappings = $this->map->Mappings;

        foreach ( $this->movers as $mover => $enabled )
        {
            if ( !$enabled ) continue;
            (new $mover($this, $mappings))->run();
        }
    }

    public function cleanModulesBefore()
    {
        foreach ( $this->map->Modules as $module )
        {
            (new ModuleCleanerBefore($module))->run();
        }
    }

    public function cleanModulesAfter()
    {
        foreach ( $this->map->Modules as $module )
        {
            (new ModuleCleanerAfter($module))->run();
        }
    }
}
