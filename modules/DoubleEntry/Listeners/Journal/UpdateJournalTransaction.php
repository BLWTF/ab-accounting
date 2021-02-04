<?php

namespace Modules\DoubleEntry\Listeners\Journal;

use App\Jobs\Banking\CreateTransaction;
use App\Jobs\Banking\DeleteTransaction;
use App\Jobs\Banking\UpdateTransaction;
use App\Models\Setting\Category;
use App\Traits\Jobs;
use Modules\DoubleEntry\Events\Journal\JournalUpdated as Event;
use Modules\DoubleEntry\Traits\Journal;

class UpdateJournalTransaction
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

        if ($this->isTransfer($journal) || $this->isOpeningBalance($journal)) {
            return;
        }

        foreach ($journal->ledgers as $ledger) {
            if (!$bank = $this->getBank($ledger)) {
                continue;
            }

            list($field, $type) = $this->getConstants($ledger);

            if ($transaction = $this->getTransaction($ledger)) {
                $request = [
                    'account_id' => $bank->bank_id,
                    'paid_at' => $journal->paid_at,
                    'description' => $journal->description,
                    'amount' => $ledger->$field,
                ];

                $this->dispatch(new UpdateTransaction($transaction, $request));

                continue;
            }

            $request = [
                'company_id' => $journal->company_id,
                'type' => $type,
                'account_id' => $bank->bank_id,
                'paid_at' => $journal->paid_at,
                'currency_code' => setting('default.currency'),
                'currency_rate' => '1',
                'description' => $journal->description,
                'payment_method' => setting('default.payment_method'),
                'amount' => $ledger->$field,
                'category_id' => Category::where('type', $type)->enabled()->pluck('id')->first(),
                'reference' => 'journal-entry-ledger:' . $ledger->id,
            ];

            $this->dispatch(new CreateTransaction($request));
        }

        $deletedLedgers = $journal->ledgers()->onlyTrashed()->get();

        foreach ($deletedLedgers as $deletedLedger) {
            if ($transaction = $this->getTransaction($deletedLedger)) {
                $this->dispatch(new DeleteTransaction($transaction));
            }
        }
    }
}
