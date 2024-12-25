<?php

namespace Bo\Generators\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class RequestBoCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bo:cms:request';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bo:cms:request
    {plugin_name : Plugin name}
    {name : Request name}
    {namespace_request : Namespace request...}
    {--make_with_plugin : force check plugin exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Request for BoCMS';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/request.stub';
    }

    /**
     * Handle make request
     * */
    public function handle()
    {
        $name = $this->getNameInput();
        $plugin_name = $this->argument('plugin_name');
        $namespace_request = $this->argument('namespace_request');

        $path = get_path_src_plugin($plugin_name, "Http/Requests" . DIRECTORY_SEPARATOR . $name);

        if (!plugin_exist($plugin_name) && !$this->option('make_with_plugin')) {
            $this->error("Plugin does not exist");
            return self::FAILURE;
        }

        if ($this->checkExits($path)) {
            $this->error("$this->type $name already existed in \"$path\" !");
            return false;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->sortImports($this->buildClassCustom($name, $namespace_request)));

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
     * @param string $class_name
     * @param string $namespace_request
     * @return string
     */
    protected function buildClassCustom(string $class_name, string $namespace_request): string
    {
        $stub = $this->files->get($this->getStub());

        $stub = str_replace('DummyClass', $class_name, $stub);
        return str_replace('DummyNamespace', $namespace_request, $stub);
    }
}
