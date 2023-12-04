<?php

namespace Fouladgar\EloquentBuilder\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eloquent-builder:publish {--force : Overwrite any existing files.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publishes EloquentBuilder configuration file to config directory of app';

    /**
     * Filesystem instance for fs operations.
     */
    protected Filesystem $files;

    /**
     * A list of files (source => destination).
     */
    protected array $fileMap = [];

    /**
     * PublishCommand constructor.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
        $fromPath = __DIR__ . '/../..';

        $this->fileMap = [
            $fromPath . '/config/eloquent-builder.php' => app()->basePath('config/eloquent-builder.php'),
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        foreach ($this->fileMap as $from => $to) {
            if ($this->files->exists($to) && !$this->option('force')) {
                continue;
            }

            $this->createParentDirectory(dirname($to));
            $this->files->copy($from, $to);
            $this->status($from, $to);
        }
    }

    /**
     * Create the directory to house the published files if needed.
     *
     * @codeCoverageIgnore
     */
    protected function createParentDirectory(string $directory): void
    {
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Write a status message to the console.
     */
    protected function status(string $from, string $to): void
    {
        $from = str_replace(base_path(), '', realpath($from));
        $to = str_replace(base_path(), '', realpath($to));
        $this->line("<info>Copied File</info> <comment>[{$from}]</comment> <info>To</info> <comment>[{$to}]</comment>");
    }
}
