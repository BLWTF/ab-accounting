<?php

namespace Modules\DoubleEntry\Jobs\Journal;

use App\Abstracts\Job;
use App\Traits\Relationships;
use Modules\DoubleEntry\Events\Journal\JournalUpdated;
use Modules\DoubleEntry\Models\Ledger;

class UpdateJournalEntry extends Job
{
    use Relationships;

    protected $request;

    protected $journalEntry;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($journalEntry, $request)
    {
        $this->journalEntry = $journalEntry;
        $this->request = $this->getRequestInstance($request);
    }

    /**
     * Execute the job.
     *
     * @return JournalEntry
     */
    public function handle()
    {
        \DB::transaction(function () {
            $input = $this->request->input();
            $amount = 0;
            $input['amount'] = $amount;
            $ledgers = [];

            $this->journalEntry->update($input);

            foreach ($input['items'] as $item) {
                $ledger = Ledger::find($item['id']);

                if ($ledger) {
                    if (!empty($item['debit'])) {
                        $ledger->update([
                            'company_id' => $this->journalEntry->company_id,
                            'account_id' => $item['account_id'],
                            'issued_at' => $this->journalEntry->paid_at,
                            'entry_type' => 'item',
                            'debit' => $item['debit'],
                        ]);

                        $amount += $item['debit'];
                    } else {
                        $ledger->update([
                            'company_id' => $this->journalEntry->company_id,
                            'account_id' => $item['account_id'],
                            'issued_at' => $this->journalEntry->paid_at,
                            'entry_type' => 'item',
                            'credit' => $item['credit'],
                        ]);
                    }

                    array_push($ledgers, $ledger->id);

                    continue;
                }

                if (!empty($item['debit'])) {
                    $ledger = $this->journalEntry->ledger()->create([
                        'company_id' => $this->journalEntry->company_id,
                        'account_id' => $item['account_id'],
                        'issued_at' => $this->journalEntry->paid_at,
                        'entry_type' => 'item',
                        'debit' => $item['debit'],
                    ]);

                    $amount += $item['debit'];
                } else {
                    $ledger = $this->journalEntry->ledger()->create([
                        'company_id' => $this->journalEntry->company_id,
                        'account_id' => $item['account_id'],
                        'issued_at' => $this->journalEntry->paid_at,
                        'entry_type' => 'item',
                        'credit' => $item['credit'],
                    ]);
                }

                array_push($ledgers, $ledger->id);
            }

            foreach ($this->journalEntry->ledgers as $ledger) {
                if (!in_array($ledger->id, $ledgers)) {
                    $ledger->delete();
                }
            }

            $this->journalEntry->amount = $amount;
            $this->journalEntry->save();
        });

        event(new JournalUpdated($this->journalEntry, $this->request));

        return $this->journalEntry;
    }
}
