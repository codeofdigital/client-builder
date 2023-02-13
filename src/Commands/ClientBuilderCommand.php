<?php

namespace CodeOfDigital\ClientBuilder\Commands;

use Illuminate\Console\GeneratorCommand;

class ClientBuilderCommand extends GeneratorCommand
{
    public $signature = 'make:client {name} {--cache : Whether to use caching in your client builder class}';

    public $description = 'Create a new Client builder class for API request calls';

    protected $type = 'Client Builder';

    protected function getStub(): string
    {
        if ($this->option('cache')) return __DIR__ . '/stubs/cache-client-builder.stub';
        else return __DIR__ . '/stubs/client-builder.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Builder';
    }
}