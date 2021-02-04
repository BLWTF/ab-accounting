<?php

namespace Modules\DoubleEntry\Listeners;

use App\Events\Menu\AdminCreated as Event;
use App\Models\Module\Module;

class AddMenu
{
    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        $module = Module::alias('double-entry')->enabled()->first();

        if (!$module) {
            return;
        }

        $user = user();

        if (!$user->can([
            'read-double-entry-chart-of-accounts',
            'read-double-entry-journal-entry',
        ])) {
            return;
        }

        $attr = [];

        $event->menu->dropdown(trans('double-entry::general.name'), function ($sub) use($user, $attr) {
            if ($user->can('read-double-entry-chart-of-accounts')) {
                $sub->url('double-entry/chart-of-accounts', trans_choice('double-entry::general.chart_of_accounts', 2), 1, $attr);
            }

            if ($user->can('read-double-entry-journal-entry')) {
                $sub->url('double-entry/journal-entry', trans('double-entry::general.journal_entry'), 2, $attr);
            }
        }, 51, [
            'title' => trans('double-entry::general.name'),
            'icon' => 'fa fa-balance-scale',
        ]);
    }
}
