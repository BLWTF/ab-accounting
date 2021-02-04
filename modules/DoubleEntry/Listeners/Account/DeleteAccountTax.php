<?php

namespace Modules\DoubleEntry\Listeners\Account;

use App\Jobs\Setting\DeleteTax;
use App\Traits\Jobs;
use Modules\DoubleEntry\Events\Account\AccountDeleted as Event;

class DeleteAccountTax
{
    use Jobs;

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle(Event $event)
    {
        $account = $event->account;

        if ($account->type_id != setting('double-entry.types_tax', 17)) {
            return;
        }

        $this->dispatch(new DeleteTax($account->tax->tax));

        $account->tax->delete();
    }
}
