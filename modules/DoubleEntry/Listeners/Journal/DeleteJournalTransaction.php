<?php

namespace Modules\DoubleEntry\Listeners\Journal;

use App\Jobs\Banking\DeleteTransaction;
use App\Traits\Jobs;
use Modules\DoubleEntry\Events\Journal\JournalDeleted as Event;
use Modules\DoubleEntry\Traits\Journal;

class DeleteJournalTransaction
{
    use Jobs, Journal;

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle(Event $event)
    {
        $journal = $event->journal;

        foreach ($journal->ledgers as $ledger) {
            if ($transaction = $this->getTransaction($ledger)) {
                $this->dispatch(new DeleteTransaction($transaction));
            }
        }
    }
}
