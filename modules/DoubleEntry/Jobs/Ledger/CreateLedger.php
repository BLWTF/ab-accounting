<?php

namespace Modules\DoubleEntry\Jobs\Ledger;

use App\Abstracts\Job;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Events\Ledger\LedgerCreated;

class CreateLedger extends Job
{
    protected $request;

    protected $ledger;

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
     * @return Ledger
     */
    public function handle()
    {
        \DB::transaction(function () {
            $this->ledger = Ledger::create($this->request->all());
        });

        event(new LedgerCreated($this->ledger));

        return $this->ledger;
    }
}
