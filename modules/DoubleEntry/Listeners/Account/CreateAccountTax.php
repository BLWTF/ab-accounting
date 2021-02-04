<?php

namespace Modules\DoubleEntry\Listeners\Account;

use App\Jobs\Setting\CreateTax;
use App\Traits\Jobs;
use Modules\DoubleEntry\Events\Account\AccountCreated as Event;
use Modules\DoubleEntry\Models\AccountTax;

class CreateAccountTax
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

        if ($account->type_id != setting('double-entry.types_tax', 17)) {
            return;
        }

        $request = [
            'company_id' => $account->company_id,
            'name' => $account->name,
            'rate' => 0,
            'enabled' => $account->enabled,
        ];

        $tax = $this->dispatch(new CreateTax($request));

        AccountTax::create([
            'company_id' => $account->company_id,
            'account_id' => $account->id,
            'tax_id' => $tax->id,
        ]);
    }
}
