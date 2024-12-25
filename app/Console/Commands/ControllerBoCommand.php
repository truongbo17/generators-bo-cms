<?php

namespace Bo\Generators\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;

class ControllerBoCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bo:cms:controller';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bo:cms:controller
    {plugin_name}
    {name : Request name}
    {namespace_controller : Namespace controller...}
    {class_model : Model class...}
    {class_request : Request class...}
    {--make_with_plugin : force check plugin exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Controller BoCMS CRUD controller';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/controller.stub';
    }

    /**
     * @var string $class_name_model
     * */
    protected string $class_name_model;

    /**
     * @var string $class_name_request
     * */
    protected string $class_name_request;

    /**
     * Execute the console command.
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $name = $this->getNameInput();
        $plugin_name = $this->argument('plugin_name');
        $namespace_controller = $this->argument('namespace_controller');
        $class_model = $this->argument('class_model');
        $class_request = $this->argument('class_request');

        $path = get_path_src_plugin($plugin_name, "Http/Controllers" . DIRECTORY_SEPARATOR . $name);

        if (!plugin_exist($plugin_name) && !$this->option('make_with_plugin')) {
            $this->error("Plugin does not exist");
            return self::FAILURE;
        }

        if ($this->checkExits($path)) {
            $this->error("$this->type $name already existed in \"$path\" !");
            return false;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->sortImports($this->buildClassCustom($plugin_name, $name, $namespace_controller, $class_model, $class_request)));

        $this->info("$this->type created successfully in " . realpath($path));

        return self::SUCCESS;
    }

    /**
     * Build the class with the given name.
     *
     * @param string $plugin_name
     * @param string $name
     * @param string $namespace_controller
     * @param $class_model
     * @param $class_request
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClassCustom(string $plugin_name, string $name, string $namespace_controller, $class_model, $class_request): string
    {
        $stub = $this->files->get($this->getStub());

        $stub = str_replace('DummyClassModel', $class_model, $stub);
        $stub = str_replace('DummyClassRequest', $class_request, $stub);
        $stub = str_replace('DummyClassController', $name, $stub);
        $stub = str_replace('DummyNamespace', $namespace_controller, $stub);
        $stub = str_replace('dummy-class', $plugin_name, $stub);
        $stub = str_replace('dummy singular', $plugin_name, $stub);
        $stub = str_replace('dummy plural', Str::plural($plugin_name), $stub);

        $this->replaceSetFromDb($stub, $class_model, $plugin_name);

        return $stub;
    }

    /**
     * Replace the table name for the given stub.
     *
     * @param string $stub
     * @param string $class_model
     * @param $plugin_name
     * @return ControllerBoCommand
     */
    protected function replaceSetFromDb(string &$stub, string $class_model, $plugin_name): ControllerBoCommand
    {
        if($this->option('make_with_plugin')){
            $path = get_path_src_plugin($plugin_name, "Models" . DIRECTORY_SEPARATOR . ucfirst(Str::camel($plugin_name)));
            if(file_exists($path)){
                require_once $path;
            };
        }

        if (!class_exists($class_model)) {
            return $this;
        }

        $attributes = $this->getAttributes($class_model);

        // create an array with the needed code for defining fields
        $fields = \Arr::except($attributes, ['id', 'created_at', 'updated_at', 'deleted_at']);
        $fields = collect($fields)
            ->map(function ($field) {
                return "CRUD::field('$field');";
            })
            ->toArray();

        // create an array with the needed code for defining columns
        $columns = \Arr::except($attributes, ['id']);
        $columns = collect($columns)
            ->map(function ($column) {
                return "CRUD::column('$column');";
            })
            ->toArray();

        // replace setFromDb with actual fields and columns
        $stub = str_replace('CRUD::setFromDb(); // fields', implode(PHP_EOL . '        ', $fields), $stub);
        $stub = str_replace('CRUD::setFromDb(); // columns', implode(PHP_EOL . '        ', $columns), $stub);

        return $this;
    }

    /**
     * Get attributes model
     *
     * @param string $model
     *
     * @return mixed
     * */
    protected function getAttributes(string $model)
    {
        $model = new $model;

        // if fillable was defined, use that as the attributes
        if (count($model->getFillable())) {
            $attributes = $model->getFillable();
        } else {
            // otherwise, if guarded is used, just pick up the columns straight from the bd table
            $attributes = \Schema::getColumnListing($model->getTable());
        }

        return $attributes;
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
