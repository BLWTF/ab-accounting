<?php

namespace Modules\DoubleEntry\Events\Account;

use Illuminate\Queue\SerializesModels;

class AccountCreated
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
