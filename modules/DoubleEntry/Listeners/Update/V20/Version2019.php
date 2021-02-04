<?php

namespace Modules\DoubleEntry\Listeners\Update\V20;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\Artisan;

class Version2019 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '2.0.19';

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        if ($this->skipThisUpdate($event)) {
            return;
        }

        $this->updateDatabase();
    }

    protected function updateDatabase()
    {
        Artisan::call('migrate', ['--force' => true]);
    }
}
