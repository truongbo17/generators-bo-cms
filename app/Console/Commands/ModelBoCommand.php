<?php

namespace Bo\Generators\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class ModelBoCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bo:cms:model';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bo:cms:model
    {plugin_name : Plugin name}
    {name : Model name}
    {table : Table name...}
    {namespace_model : Namespace model...}
    {--make_with_plugin : force check plugin exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Model BoCMS CRUD model';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * The trait that allows a model to have an admin panel.
     *
     * @var string
     */
    protected string $crudTrait = 'Bo\Base\Models\Traits\CrudTrait';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/model.stub';
    }

    /**
     * Table name
     *
     * @var string
     * */
    protected string $table_name;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->getNameInput();
        $plugin_name = $this->argument('plugin_name');
        $table = $this->argument('table');
        $namespace_model = $this->argument('namespace_model');

        $path = get_path_src_plugin($plugin_name, "Models" . DIRECTORY_SEPARATOR . $name);

        if (!plugin_exist($plugin_name) && !$this->option('make_with_plugin')) {
            $this->error("Plugin does not exist");
            return self::FAILURE;
        }

        if ($this->checkExits($path)) {
            $this->error("$this->type $name already existed in \"$path\" !");
            return false;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->sortImports($this->buildClassCustom($name, $namespace_model, $table)));

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
     * @param string $namespace_model
     * @param string $table_name
     * @return string
     */
    protected function buildClassCustom(string $class_name, string $namespace_model, string $table_name): string
    {
        $stub = $this->files->get($this->getStub());

        $stub = str_replace('DummyClass', $class_name, $stub);
        $stub = str_replace('DummyNamespace', $namespace_model, $stub);
        return str_replace('DummyTable', $table_name, $stub);
    }

}
