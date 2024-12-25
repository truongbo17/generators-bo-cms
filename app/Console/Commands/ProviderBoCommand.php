<?php

namespace Bo\Generators\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class ProviderBoCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bo:cms:provider';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bo:cms:provider
    {plugin_name : Plugin name}
    {name : Provider name}
    {namespace_provider : Namespace provider...}
    {--make_with_plugin : force check plugin exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Provider BoCMS CRUD model';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Provider';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->getNameInput();
        $plugin_name = $this->argument('plugin_name');
        $namespace_provider = $this->argument('namespace_provider');

        $path = get_path_src_plugin($plugin_name, "Providers" . DIRECTORY_SEPARATOR . $name);

        if (!plugin_exist($plugin_name) && !$this->option('make_with_plugin')) {
            $this->error("Plugin does not exist");
            return self::FAILURE;
        }

        if ($this->checkExits($path)) {
            $this->error("$this->type $name already existed in \"$path\" !");
            return false;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->sortImports($this->buildClassCustom($plugin_name, $name, $namespace_provider)));

        $this->info("$this->type created successfully in " . realpath($path));

        return self::SUCCESS;
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

    /**
     * Build the class with the given name.
     *
     * @param string $plugin_name
     * @param string $class_name
     * @param string $namespace_provider
     * @return string
     */
    protected function buildClassCustom(string $plugin_name, string $class_name, string $namespace_provider): string
    {
        $stub = $this->files->get($this->getStub());

        $stub = str_replace('DummyClass', $class_name, $stub);
        $stub = str_replace('DummyNamespace', $namespace_provider, $stub);
        $stub = str_replace('DummyName', $plugin_name, $stub);
        return str_replace('DummyLabel', ucfirst($plugin_name), $stub);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/provider.stub';
    }

}
