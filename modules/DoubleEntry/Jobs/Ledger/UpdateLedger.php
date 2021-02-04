<?php

namespace Modules\DoubleEntry\Jobs\Ledger;

use App\Abstracts\Job;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Events\Ledger\LedgerUpdated;

class UpdateLedger extends Job
{
    protected $request;

    protected $ledger;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ledger, $request)
    {
        $this->ledger = $ledger;
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
            $this->ledger->update($this->request->all());
        });

        event(new LedgerUpdated($this->ledger, $this->request));

        return $this->ledger;
    }
}
