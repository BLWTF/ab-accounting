<?php

namespace Modules\DoubleEntry\Jobs\Ledger;

use App\Abstracts\Job;
use Modules\DoubleEntry\Events\Ledger\LedgerDeleted;

class DeleteLedger extends Job
{
    protected $ledger;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ledger)
    {
        $this->ledger = $ledger;
    }

    /**
     * Execute the job.
     *
     * @return Ledger
     */
    public function handle()
    {
        \DB::transaction(function () {
            $this->ledger->delete();
        });

        event(new LedgerDeleted($this->ledger));

        return true;
    }
}
