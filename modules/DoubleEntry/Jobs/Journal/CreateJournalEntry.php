<?php

namespace Modules\DoubleEntry\Jobs\Journal;

use App\Abstracts\Job;
use Modules\DoubleEntry\Events\Journal\JournalCreated;
use Modules\DoubleEntry\Models\Journal;

class CreateJournalEntry extends Job
{
    protected $request;

    protected $journalEntry;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
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

            $this->journalEntry = Journal::create($input);

            foreach ($input['items'] as $item) {
                if (!empty($item['debit'])) {
                    $this->journalEntry->ledger()->create([
                        'company_id' => $this->journalEntry->company_id,
                        'account_id' => $item['account_id'],
                        'issued_at' => $this->journalEntry->paid_at,
                        'entry_type' => 'item',
                        'debit' => $item['debit'],
                    ]);

                    $amount += $item['debit'];
                } else {
                    $this->journalEntry->ledger()->create([
                        'company_id' => $this->journalEntry->company_id,
                        'account_id' => $item['account_id'],
                        'issued_at' => $this->journalEntry->paid_at,
                        'entry_type' => 'item',
                        'credit' => $item['credit'],
                    ]);
                }
            }

            $this->journalEntry->amount = $amount;
            $this->journalEntry->save();
        });

        event(new JournalCreated($this->journalEntry));

        return $this->journalEntry;
    }
}
