<?php

namespace AbTesting\Tests;

use AbTesting\AbTestingFacade;
use AbTesting\Commands\ReportCommand;
use AbTesting\Models\DatabaseVisitor;
use AbTesting\Models\Goal;
use AbTesting\Models\Variant;

class CommandTest extends AbTestCase
{
    public function test_flush_command()
    {
        DatabaseVisitor::truncate();

        $this->assertCount(0, Variant::all());
        $this->assertCount(0, Goal::all());
        $this->assertCount(0, DatabaseVisitor::all());

        AbTestingFacade::pageView(123);

        $this->assertCount(2, Variant::all());
        $this->assertCount(4, Goal::all());
        $this->assertCount(1, DatabaseVisitor::all());

        $this->artisan('ab:reset');

        $this->assertCount(0, Variant::all());
        $this->assertCount(0, Goal::all());
        $this->assertCount(0, DatabaseVisitor::all());
    }

    public function test_report_command()
    {
        if (version_compare(app()->version(), '5.7.0') >= 0) {
            $this->artisan('ab:report')->assertExitCode(0);
        }

        $reportCommand = new ReportCommand();

        $this->assertEquals([
            'Variant',
            'Visitors',
            'Goal firstGoal',
            'Goal secondGoal',
        ], $reportCommand->prepareHeader());

        $this->assertEquals([], $reportCommand->prepareBody()->toArray());

        AbTestingFacade::pageView();

        $expected = [
            [
                'firstVariant',
                1,
                '0 (0%)',
                '0 (0%)',
            ],
            [
                'secondVariant',
                0,
                '0 (0%)',
                '0 (0%)',
            ],
        ];
        $this->assertEquals($expected, $reportCommand->prepareBody()->toArray());

        $this->newVisitor();

        $expected = [
            [
                'firstVariant',
                1,
                '0 (0%)',
                '0 (0%)',
            ],
            [
                'secondVariant',
                1,
                '0 (0%)',
                '0 (0%)',
            ],
        ];
        $this->assertEquals($expected, $reportCommand->prepareBody()->toArray());

        AbTestingFacade::completeGoal('firstGoal');

        $expected = [
            [
                'firstVariant',
                1,
                '0 (0%)',
                '0 (0%)',
            ],
            [
                'secondVariant',
                1,
                '1 (100%)',
                '0 (0%)',
            ],
        ];
        $this->assertEquals($expected, $reportCommand->prepareBody()->toArray());

        $this->newVisitor();
        $this->newVisitor();
        $this->newVisitor();

        $expected = [
            [
                'firstVariant',
                2,
                '0 (0%)',
                '0 (0%)',
            ],
            [
                'secondVariant',
                3,
                '1 (33%)',
                '0 (0%)',
            ],
        ];
        $this->assertEquals($expected, $reportCommand->prepareBody()->toArray());
    }
}
