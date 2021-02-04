<?php

namespace Modules\DoubleEntry\Events\Account;

use Illuminate\Queue\SerializesModels;

class AccountUpdated
{
    use SerializesModels;

    public $account;

    public $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($account, $request)
    {
        $this->account = $account;
        $this->request = $request;
    }
}
