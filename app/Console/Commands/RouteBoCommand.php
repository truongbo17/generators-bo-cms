<?php

namespace Bo\Generators\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class RouteBoCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bo:cms:route';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bo:cms:route
    {plugin_name : Plugin name}
    {name : Route file name}
    {class_controller : Class controller use in route...}
    {namespace_controller : Namespace controller...}
    {--make_with_plugin : force check plugin exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Route custom for BoCMS';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Route';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/route.stub';
    }

    /**
     * Handle make route
     * */
    public function handle()
    {
        $name = $this->getNameInput();
        $plugin_name = $this->argument('plugin_name');
        $class_controller = $this->argument('class_controller');
        $namespace_controller = $this->argument('namespace_controller');

        $path = get_path_route_plugin($plugin_name, $name);

        if (!plugin_exist($plugin_name) && !$this->option('make_with_plugin')) {
            $this->error("Plugin does not exist");
            return self::FAILURE;
        }

        if ($this->checkExits($path)) {
            $this->error("$this->type $name already existed in \"$path\" !");
            return false;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->sortImports($this->buildClassCustom($class_controller, $namespace_controller, $plugin_name)));

        $this->info("$this->type created successfully in " . realpath($path));

        return self::SUCCESS;
    }

    /**
     * Build the class with the given name.
     *
     * @param string $class_controller
     * @param string $namespace_controller
     * @param string $plugin_name
     *
     * @return string
     */
    protected function buildClassCustom(string $class_controller, string $namespace_controller, string $plugin_name): string
    {
        $stub = $this->files->get($this->getStub());

        $stub = str_replace('plugin_route', $plugin_name, $stub);
        $stub = str_replace('namespace_plugin_controller', $namespace_controller, $stub);
        return str_replace('plugin_controller', $class_controller, $stub);
    }

    /**
     * Check exist file or directory
     *
     * @param string $path_name
     * @return bool
     */
    public function checkExits(string $path_name): bool
    {
        return $this->files->exists($path_name);
    }
}
