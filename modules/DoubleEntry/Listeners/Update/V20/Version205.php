<?php

namespace Modules\DoubleEntry\Listeners\Update\V20;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\DB;

class Version205 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '2.0.5';

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
        DB::table('double_entry_types')
            ->where('name', 'double-entry::types.depreciation')
            ->update([
                'class_id' => '1',
            ]);
    }
}
