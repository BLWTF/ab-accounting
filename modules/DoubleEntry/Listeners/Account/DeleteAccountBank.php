<?php

namespace Modules\DoubleEntry\Listeners\Account;

use App\Jobs\Banking\DeleteAccount;
use App\Traits\Jobs;
use Modules\DoubleEntry\Events\Account\AccountDeleted as Event;

class DeleteAccountBank
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

        if ($account->type_id != setting('double-entry.types_bank', 6)) {
            return;
        }

        $this->dispatch(new DeleteAccount($account->bank->bank));

        $account->bank->delete();
    }
}
