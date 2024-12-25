<?php

namespace Bo\Generators\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class LangBoCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bo:cms:lang';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bo:cms:lang
    {plugin_name : plugin name}
    {name : config file name}
    {lang : language}
    {--make_with_plugin : force check plugin exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a lang file plugin for BoCMS';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Lang';

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
        $lang = $this->argument('lang');
        $path = get_path_resource_plugin($plugin_name, "lang" . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $name);

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
        return $this->files->get($this->getStub());
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/lang.stub';
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
