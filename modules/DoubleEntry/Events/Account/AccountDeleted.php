<?php

namespace Modules\DoubleEntry\Events\Account;

use Illuminate\Queue\SerializesModels;

class AccountDeleted
{
    use SerializesModels;

    public $account;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($account)
    {
        $this->account = $account;
    }
}
