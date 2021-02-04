<?php

namespace Modules\DoubleEntry\Jobs\Account;

use App\Abstracts\Job;
use Modules\DoubleEntry\Events\Account\AccountDeleted;

class DeleteAccount extends Job
{
    protected $account;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($account)
    {
        $this->account = $account;
    }

    /**
     * Execute the job.
     *
     * @return Account
     */
    public function handle()
    {
        \DB::transaction(function () {
            $this->account->delete();
        });

        event(new AccountDeleted($this->account));

        return true;
    }
}
