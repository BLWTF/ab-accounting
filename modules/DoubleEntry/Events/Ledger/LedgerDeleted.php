<?php

namespace Modules\DoubleEntry\Events\Ledger;

use Illuminate\Queue\SerializesModels;

class LedgerDeleted
{
    use SerializesModels;

    public $ledger;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($ledger)
    {
        $this->ledger = $ledger;
    }
}
