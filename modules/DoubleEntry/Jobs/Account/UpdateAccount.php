<?php

namespace Modules\DoubleEntry\Jobs\Account;

use App\Abstracts\Job;
use App\Models\Banking\Account;
use Modules\DoubleEntry\Events\Account\AccountUpdated;

class UpdateAccount extends Job
{
    protected $request;

    protected $account;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($account, $request)
    {
        $this->account = $account;
        $this->request = $this->getRequestInstance($request);
    }

    /**
     * Execute the job.
     *
     * @return Account
     */
    public function handle()
    {
        \DB::transaction(function () {
            $this->request['code'] = !empty($this->request['code']) ? $this->request['code'] : $this->account->code;
            $this->request['type_id'] = !empty($this->request['type_id']) ? $this->request['type_id'] : $this->account->type_id;

            $lang = array_flip(trans('double-entry::accounts'));

            if (!empty($lang[$this->request['name']])) {
                $this->request['name'] = 'double-entry::accounts.' . $lang[$this->request['name']];
            }

            $this->account->update($this->request->all());
        });

        event(new AccountUpdated($this->account, $this->request));

        return $this->account;
    }
}
