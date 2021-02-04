<?php

namespace Modules\DoubleEntry\Events\Ledger;

use Illuminate\Queue\SerializesModels;

class LedgerUpdated
{
    use SerializesModels;

    public $ledger;

    public $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($ledger, $request)
    {
        $this->ledger = $ledger;
        $this->request = $request;
    }
}
