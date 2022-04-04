<?php

namespace AbTesting\Tests;

use AbTesting\AbTestingFacade;
use AbTesting\Exceptions\InvalidConfiguration;
use AbTesting\Models\Goal;
use AbTesting\Models\Variant;

class StartTest extends AbTestCase
{
    public function test_that_start_function_works()
    {
        AbTestingFacade::pageView();

        $this->assertCount(count($this->variants), Variant::all());
        $this->assertCount(count($this->goals) * count($this->variants), Goal::all());

        $everyVariantsVisitorsIsInt = Variant::all()->every(function ($variant) {
            return is_int($variant->visitors);
        });
        $this->assertTrue($everyVariantsVisitorsIsInt);

        $everyGoalsHitIs0 = Goal::all()->every(function ($goal) {
            return $goal->hit === 0;
        });
        $this->assertTrue($everyGoalsHitIs0);
    }

    public function test_exception_if_duplicate_variant_names()
    {
        config([
            'ab-testing.variants' => [
                'test',
                'test',
            ],
        ]);

        $this->expectException(InvalidConfiguration::class);

        AbTestingFacade::pageView();
    }

    public function test_exception_if_duplicate_goal_names()
    {
        config([
            'ab-testing.goals' => [
                'test',
                'test',
            ],
        ]);

        $this->expectException(InvalidConfiguration::class);

        AbTestingFacade::pageView();
    }

    public function test_exception_if_no_variants_set()
    {
        config([
            'ab-testing.variants' => [],
        ]);

        $this->expectException(InvalidConfiguration::class);

        AbTestingFacade::pageView();
    }
}
