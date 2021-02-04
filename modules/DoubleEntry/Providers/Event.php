<?php

namespace Modules\DoubleEntry\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\DoubleEntry\Listeners\DocumentCloned;

class Event extends ServiceProvider
{
    /**
     * The event listener mappings for the module.
     *
     * @var array
     */
    protected $listen = [
        'cloner::cloned: App\Models\Document\Document' => [
            DocumentCloned::class,
        ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
