<?php

namespace Bo\Generators\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class MigrationBoCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bo:cms:migration';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bo:cms:migration
    {plugin_name : plugin name}
    {name : config file name}
    {--make_with_plugin : force check plugin exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a migration file plugin for BoCMS';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Migration';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/migration.stub';
    }

    /**
     * Handle create config file
     *
     * @return false|int
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $name = $this->getNameInput();
        $plugin_name = $this->argument('plugin_name');

        if ($this->option('make_with_plugin')) {
            $migration_name = Carbon::now()->format("Y_m_d_His") . "_create_" . $name . "_table";
        } else {
            $migration_name = $name;
        }

        $path = get_path_database_plugin($plugin_name, $migration_name);

        if (!plugin_exist($plugin_name) && !$this->option('make_with_plugin')) {
            $this->error("Plugin does not exist");
            return self::FAILURE;
        }

        if ($this->checkExits($path)) {
            $this->error("$this->type $name already existed in \"$path\" !");
            return false;
        }

        $this->makeDirectory($path);
        $this->files->put($path, $this->buildClass($name));
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
     * @param string $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceTable($stub, $name);
    }

    /**
     * Replace the table name for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return array|string|string[]
     */
    private function replaceTable(string &$stub, string $name)
    {
        return str_replace('dummy-table', $name, $stub);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [

        ];
    }
}
