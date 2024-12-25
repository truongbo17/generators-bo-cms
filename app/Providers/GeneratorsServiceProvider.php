<?php

namespace Bo\Generators\Providers;

use Bo\Generators\Console\Commands\ConfigBoCommand;
use Bo\Generators\Console\Commands\ControllerBoCommand;
use Bo\Generators\Console\Commands\HelperBoCommand;
use Bo\Generators\Console\Commands\LangBoCommand;
use Bo\Generators\Console\Commands\MakePluginCommand;
use Bo\Generators\Console\Commands\MigrationBoCommand;
use Bo\Generators\Console\Commands\ModelBoCommand;
use Bo\Generators\Console\Commands\ProviderBoCommand;
use Bo\Generators\Console\Commands\RequestBoCommand;
use Bo\Generators\Console\Commands\RouteBoCommand;
use Bo\Generators\Console\Commands\ViewBoCommand;
use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{
    protected array $commands = [
        MakePluginCommand::class,
        ConfigBoCommand::class,
        HelperBoCommand::class,
        MigrationBoCommand::class,
        LangBoCommand::class,
        ViewBoCommand::class,
        RequestBoCommand::class,
        ModelBoCommand::class,
        ControllerBoCommand::class,
        RouteBoCommand::class,
        ProviderBoCommand::class,
    ];

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }
}
