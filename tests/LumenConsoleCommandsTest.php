<?php

namespace Fouladgar\EloquentBuilder\Tests;

class LumenConsoleCommandsTest extends TestCase
{
    use LumenServiceRegister;

    public function test_publish_console_command()
    {
        $this->artisan('eloquent-builder:publish')
             ->assertExitCode(0);
    }

    public function test_publish_command_with_force_option()
    {
        $this->artisan('eloquent-builder:publish --force')
             ->assertExitCode(0);
    }
}
