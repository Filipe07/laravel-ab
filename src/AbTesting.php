<?php

namespace AbTesting;

use AbTesting\Contracts\VisitorInterface;
use AbTesting\Events\GoalCompleted;
use AbTesting\Events\VariantNewVisitor;
use AbTesting\Exceptions\InvalidConfiguration;
use AbTesting\Models\DatabaseVisitor;
use AbTesting\Models\Goal;
use AbTesting\Models\SessionVisitor;
use AbTesting\Models\Variant;
use Illuminate\Support\Collection;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class AbTesting
{
    protected $variants;
    protected $visitor;

    public const SESSION_KEY_GOALS = 'ab_testing_goals';

    public function __construct()
    {
        $this->variants = new Collection();
    }

    /**
     * Validates the config items and puts them into models.
     */
    protected function start()
    {
        $configVariants = config('ab-testing.variants');
        $configGoals = config('ab-testing.goals');

        if (!count($configVariants)) {
            throw InvalidConfiguration::noVariant();
        }

        if (count($configVariants) !== count(array_unique($configVariants))) {
            throw InvalidConfiguration::variant();
        }

        if (count($configGoals) !== count(array_unique($configGoals))) {
            throw InvalidConfiguration::goal();
        }

        foreach ($configVariants as $configVariant) {
            $this->variants[] = $variant = Variant::with('goals')->firstOrCreate([
                'name' => $configVariant,
            ], [
                'visitors' => 0,
            ]);

            foreach ($configGoals as $configGoal) {
                $variant->goals()->firstOrCreate([
                    'name' => $configGoal,
                ], [
                    'hit' => 0,
                ]);
            }
        }

        session([
            self::SESSION_KEY_GOALS => new Collection(),
        ]);
    }

    /**
     * Resets the visitor data.
     */
    public function resetVisitor()
    {
        session()->flush();
        $this->visitor = null;
    }

    /**
     * Triggers a new visitor. Picks a new variant and saves it to the Visitor.
     *
     * @param string $visitor_id An optional visitor identifier
     *
     * @return \AbTesting\Models\Variant|void
     */
    public function pageView($visitor_id = null)
    {
        if (config('ab-testing.ignore_crawlers') && (new CrawlerDetect())->isCrawler()) {
            return false;
        }

        $visitor = $this->getVisitor($visitor_id);

        if (!session(self::SESSION_KEY_GOALS) || $this->variants->isEmpty()) {
            $this->start();
        }

        if ($visitor->hasVariant()) {
            return $visitor->getVariant();
        }

        $this->setNextVariant($visitor);

        event(new VariantNewVisitor($this->getVariant(), $visitor));

        return $this->getVariant();
    }

    /**
     * Calculates a new variant and sets it to the Visitor.
     *
     * @param VisitorInterface $visitor An object implementing VisitorInterface
     */
    protected function setNextVariant(VisitorInterface $visitor)
    {
        $next = $this->getNextVariant();
        $next->incrementVisitor();

        $visitor->setVariant($next);
    }

    /**
     * Calculates a new variant.
     *
     * @return \AbTesting\Models\Variant
     */
    protected function getNextVariant()
    {
        $sorted = $this->variants->sortBy('visitors');

        return $sorted->first();
    }

    /**
     * Checks if the currently active variant is the given one.
     *
     * @param string $name The variants name
     *
     * @return bool
     */
    public function isVariant(string $name)
    {
        $variant = $this->pageView();

        if (!$variant) {
            return false;
        }

        return $variant->name === $name;
    }

    /**
     * Completes a goal by incrementing the hit property of the model and setting its ID in the session.
     *
     * @param string $goal       The goals name
     * @param string $visitor_id An optional visitor identifier
     *
     * @return \AbTesting\Models\Goal|false
     */
    public function completeGoal(string $goal, $visitor_id = null)
    {
        $variant = $this->pageView($visitor_id);

        if (!$variant) {
            return false;
        }

        $goal = $this->getVariant($visitor_id)->goals->where('name', $goal)->first();

        if (!$goal) {
            return false;
        }

        if (session(self::SESSION_KEY_GOALS)->contains($goal->id)) {
            return false;
        }

        session(self::SESSION_KEY_GOALS)->push($goal->id);

        $goal->incrementHit();
        event(new GoalCompleted($goal));

        return $goal;
    }

    /**
     * Returns the currently active variant.
     *
     * @param string $visitor_id An optional visitor identifier
     *
     * @return \AbTesting\Models\Variant|null
     */
    public function getVariant($visitor_id = null)
    {
        return $this->getVisitor($visitor_id)->getVariant();
    }

    /**
     * Returns all the completed goals.
     *
     * @return false|\Illuminate\Support\Collection
     */
    public function getCompletedGoals()
    {
        if (!session(self::SESSION_KEY_GOALS)) {
            return false;
        }

        return session(self::SESSION_KEY_GOALS)->map(function ($goalId) {
            return Goal::find($goalId);
        });
    }

    /**
     * Returns a visitor instance. Sugestion: use uniqid() as visitor_id.
     *
     * @param string $visitor_id An optional visitor identifier
     *
     * @return \AbTesting\Models\DatabaseVisitor|\AbTesting\Models\SessionVisitor
     */
    public function getVisitor($visitor_id = null)
    {
        if (!is_null($this->visitor)) {
            return $this->visitor;
        }

        if (!empty($visitor_id) && \config('ab-testing.use_database')) {
            return $this->visitor = DatabaseVisitor::firstOrNew(['visitor_id' => $visitor_id]);
        }

        return $this->visitor = new SessionVisitor();
    }
}
