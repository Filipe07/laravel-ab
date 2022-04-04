<?php

namespace AbTesting\Tests;

use AbTesting\AbTestingFacade;
use AbTesting\AbTestingServiceProvider;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;

class AbTestCase extends TestCase
{
    protected $variants = [
        'firstVariant',
        'secondVariant',
    ];
    protected $goals = [
        'firstGoal',
        'secondGoal',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');

        session()->flush();

        Event::fake();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'ab');
        $app['config']->set('database.connections.ab', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('ab-testing.variants', $this->variants);
        $app['config']->set('ab-testing.goals', $this->goals);
    }

    protected function getPackageProviders($app)
    {
        return [AbTestingServiceProvider::class];
    }

    protected function newVisitor()
    {
        AbTestingFacade::resetVisitor();
        AbTestingFacade::pageView();
    }
}
