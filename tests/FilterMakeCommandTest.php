<?php

namespace Fouladgar\EloquentBuilder\Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Mockery as m;

class FilterMakeCommandTest extends TestCase
{
    /**
     * @var m\Mock
     */
    protected $command;

    /**
     * Orchestra app directory path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->basePath = $this->getBasePath();

        $this->command = m::mock(
            'Fouladgar\EloquentBuilder\Console\FilterMakeCommand[info,rootNamespace,getDefaultNamespace]',
            [new Filesystem()]
        )->shouldAllowMockingProtectedMethods();

        $this->command->shouldReceive('info')->andReturn('Filter[s] created successfully.');
        $this->command->shouldReceive('rootNamespace')->andReturn('AppTest');
        $this->command->shouldReceive('getDefaultNamespace')->andReturn('AppTest\EloquentFilters\User');

        $this->app[Kernel::class]->registerCommand($this->command);
    }

    /**
     * @test
     */
    public function it_can_make_multiple_filters()
    {
        $this->artisan('eloquent-builder:make user age_more_than gender');

        $basePath = $this->basePath.'/app/EloquentFilters/User/';

        $this->assertFileExists($basePath.'AgeMoreThanFilter.php');
        $this->assertFileExists($basePath.'GenderFilter.php');
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();

        m::close();

        exec('rm -rf '.$this->basePath.'/app/EloquentFilters');
    }
}
