<?php

namespace Modules\PMB\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * @var object|string
     */
    protected $service;

    protected function prepare(): void {
        //
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ($this->service);
        $this->prepare();
    }
}
