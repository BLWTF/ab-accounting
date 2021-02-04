<?php

namespace Modules\DoubleEntry\Listeners\Update\V20;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use App\Models\Module\Module;
use App\Utilities\Overrider;

class Version2014 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '2.0.14';

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
        $company_id = session('company_id');

        $modules = Module::where('company_id', '<>', '0')->alias('double-entry')->cursor();

        foreach ($modules as $module) {
            // Set the active company settings
            setting()->setExtraColumns(['company_id' => $module->company_id]);
            setting()->forgetAll();
            setting()->load(true);
            setting()->set(['double-entry.accounts_sales_discount' => 825]);
            setting()->set(['double-entry.accounts_purchase_discount' => 475]);

            setting()->save();
        }

        setting()->forgetAll();

        session(['company_id' => $company_id]);

        Overrider::load('settings');
    }
}
