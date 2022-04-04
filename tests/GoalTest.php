<?php

namespace AbTesting\Tests;

use AbTesting\AbTesting;
use AbTesting\AbTestingFacade;
use AbTesting\Events\GoalCompleted;
use Illuminate\Support\Facades\Event;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Mockery;

class GoalTest extends AbTestCase
{
    public function test_that_goal_complete_works()
    {
        $returnedGoal = AbTestingFacade::completeGoal('firstGoal');

        $variant = AbTestingFacade::getVariant();
        $goal = $variant->goals->where('name', 'firstGoal')->first();

        $this->assertEquals($goal, $returnedGoal);
        $this->assertEquals(1, $goal->hit);
        $this->assertEquals(collect([$goal->id]), session(AbTesting::SESSION_KEY_GOALS));
        Event::assertDispatched(GoalCompleted::class, function ($g) use ($goal) {
            return $g->goal->id === $goal->id;
        });
    }

    public function test_that_visitor_id_goal_complete_works()
    {
        AbTestingFacade::pageView(123);
        AbTestingFacade::resetVisitor();

        $returnedGoal = AbTestingFacade::completeGoal('firstGoal', 123);

        $variants = AbTestingFacade::getVariant(123);
        $goal = $variants->goals->where('name', 'firstGoal')->first();

        $this->assertEquals($goal, $returnedGoal);
        $this->assertEquals(1, $goal->hit);
        $this->assertEquals(collect([$goal->id]), session(AbTesting::SESSION_KEY_GOALS));
        Event::assertDispatched(GoalCompleted::class, function ($g) use ($goal) {
            return $g->goal->id === $goal->id;
        });
    }

    public function test_that_goal_can_only_be_completed_once()
    {
        $this->test_that_goal_complete_works();

        $variants = AbTestingFacade::getVariant();
        $goal = $variants->goals->where('name', 'firstGoal')->first();

        $this->assertEquals(1, $goal->hit);

        $returnedGoal = AbTestingFacade::completeGoal('firstGoal');

        $this->assertFalse($returnedGoal);
        $this->assertEquals(1, $goal->hit);
        $this->assertEquals(collect([$goal->id]), session(AbTesting::SESSION_KEY_GOALS));
    }

    public function test_that_invalid_goal_name_returns_false()
    {
        $this->assertFalse(AbTestingFacade::completeGoal('1234'));
    }

    public function test_that_completed_goals_returns_false()
    {
        $this->assertFalse(AbTestingFacade::getCompletedGoals());
    }

    public function test_that_completed_goals_works()
    {
        AbTestingFacade::completeGoal('firstGoal');

        $variant = AbTestingFacade::getVariant();
        $goal = $variant->goals->where('name', 'firstGoal');

        $this->assertEquals($goal->pluck('id')->toArray(), AbTestingFacade::getCompletedGoals()->pluck('id')->toArray());
    }

    public function test_that_complete_goal_works_with_crawlers()
    {
        config(['ab-testing.ignore_crawlers' => true]);

        Mockery::mock(CrawlerDetect::class)->shouldReceive('isCrawler')->andReturn(true);

        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot';

        $this->assertFalse(AbTestingFacade::completeGoal('firstGoal'));

        Mockery::mock(CrawlerDetect::class)->shouldReceive('isCrawler')->andReturn(false);
        config(['ab-testing.ignore_crawlers' => false]);
    }
}
