<?php

namespace AbTesting\Tests;

use AbTesting\AbTestingFacade;
use AbTesting\Events\ExperimentNewVisitor;
use AbTesting\Events\VariantNewVisitor;
use AbTesting\Models\SessionVisitor;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;

class PageViewTest extends AbTestCase
{
    public function test_that_pageview_works()
    {
        AbTestingFacade::pageView();

        $variant = session(SessionVisitor::SESSION_KEY_VARIANT);

        $this->assertEquals($this->variants[0], $variant->name);
        $this->assertEquals(1, $variant->visitors);

        Event::assertDispatched(VariantNewVisitor::class, function ($e) use ($variant) {
            return $e->variant->id === $variant->id;
        });
    }

    public function test_that_pageview_changes_after_first_test()
    {
        $this->test_that_pageview_works();

        session()->flush();

        $this->assertNull(session(SessionVisitor::SESSION_KEY_VARIANT));

        AbTestingFacade::pageView();

        $variant = session(SessionVisitor::SESSION_KEY_VARIANT);

        $this->assertEquals($this->variants[1], $variant->name);
        $this->assertEquals(1, $variant->visitors);
    }

    public function test_that_pageview_does_not_trigger_for_crawlers()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'crawl';
        config()->set('ab-testing.ignore_crawlers', true);

        AbTestingFacade::pageView();

        Event::assertNotDispatched(VariantNewVisitor::class);
    }

    public function test_is_variant()
    {
        AbTestingFacade::pageView();

        $this->assertTrue(AbTestingFacade::isVariant('firstVariant'));
        $this->assertFalse(AbTestingFacade::isVariant('secondVariant'));

        $this->assertEquals('firstVariant', AbTestingFacade::getVariant()->name);
    }

    public function test_that_two_pageviews_do_not_count_as_two_visitors()
    {
        AbTestingFacade::pageView();
        AbTestingFacade::pageView();

        $variant = session(SessionVisitor::SESSION_KEY_VARIANT);

        $this->assertEquals(1, $variant->visitors);
    }

    public function test_that_isVariant_triggers_pageview()
    {
        AbTestingFacade::isVariant('firstVariant');

        $variant = session(SessionVisitor::SESSION_KEY_VARIANT);

        $this->assertEquals($this->variants[0], $variant->name);
        $this->assertEquals(1, $variant->visitors);
    }

    public function test_request_macro()
    {
        $this->newVisitor();

        $variant = session(SessionVisitor::SESSION_KEY_VARIANT);

        $this->assertEquals($variant, request()->abVariant());
    }

    public function test_blade_macro()
    {
        $this->newVisitor();

        $this->assertTrue(Blade::check('ab', 'firstVariant'));
    }

    public function test_that_isVariant_works_with_crawlers()
    {
        config([
            'ab-testing.ignore_crawlers' => true,
        ]);
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot';

        $this->assertFalse(AbTestingFacade::isVariant('firstVariant'));
    }
}
