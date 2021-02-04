<?php

namespace Modules\DoubleEntry\Listeners\Account;

use App\Jobs\Banking\CreateAccount;
use App\Traits\Jobs;
use Modules\DoubleEntry\Events\Account\AccountCreated as Event;
use Modules\DoubleEntry\Models\AccountBank;

class CreateAccountBank
{
    use Jobs;

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle(Event $event)
    {
        $account = $event->account;

        if ($account->type_id != setting('double-entry.types_bank', 6)) {
            return;
        }

        $request = [
            'company_id' => $account->company_id,
            'name' => $account->name,
            'number' => $account->code,
            'currency_code' => setting('default.currency'),
            'opening_balance' => 0,
            'enabled' => $account->enabled,
            'bank_name' => 'chart-of-accounts',
        ];

        $banking_account = $this->dispatch(new CreateAccount($request));

        AccountBank::create([
            'company_id' => $account->company_id,
            'account_id' => $account->id,
            'bank_id' => $banking_account->id,
        ]);
    }
}
