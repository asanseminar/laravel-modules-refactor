<?php

namespace App\Console\Commands\Refactor\Movers;

class ResponsesMover extends BaseMover
{
    private $mappings;

    public function __construct($command, $mappings)
    {
        $this->mappings = $mappings;
        parent::__construct($command);
    }


    public function run()
    {
        $this->runPhpactorClassMove( app_path("Http/Responses/ApiResponse.php"), module_path("Core", "Http/Responses/ApiResponse.php") );
        $this->runPhpactorClassMove( app_path("Http/Responses/SmartResponse.php"), module_path("Core", "Http/Responses/SmartResponse.php") );
        $this->removeFolder( app_path("Http/Responses") );
    }
}
