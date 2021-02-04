<?php

namespace Modules\DoubleEntry\Observers\Banking;

use App\Abstracts\Observer;
use App\Models\Banking\Account as Model;
use App\Traits\Jobs;
use Modules\DoubleEntry\Jobs\Journal\CreateJournalEntry;
use Modules\DoubleEntry\Jobs\Journal\DeleteJournalEntry;
use Modules\DoubleEntry\Jobs\Journal\UpdateJournalEntry;
use Modules\DoubleEntry\Models\Account as Coa;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\Journal;
use Modules\DoubleEntry\Traits\Module;

class Account extends Observer
{
    use Module, Jobs;

    /**
     * Listen to the created event.
     *
     * @param  Model  $account
     * @return void
     */
    public function created(Model $account)
    {
        if ($this->isModuleDisabled()) {
            return;
        }

        if ($account->bank_name == 'chart-of-accounts') {
            $account->bank_name = '';
            $account->save();

            return;
        }

        $coa = Coa::create([
            'company_id' => $account->company_id,
            'type_id' => setting('double-entry.types_bank', 6),
            'code' => Coa::max('code') + 1,
            'name' => $account->name,
            'enabled' => $account->enabled,
        ]);

        AccountBank::create([
            'company_id' => $account->company_id,
            'account_id' => $coa->id,
            'bank_id' => $account->id,
        ]);

        if ($account->opening_balance <= 0) {
            return;
        }

        $owner_contribution = Coa::code(setting('double-entry.accounts_owners_contribution'))->first();

        if (is_null($owner_contribution)) {
            return;
        }

        $request = [
            'company_id' => $account->company_id,
            'paid_at' => $account->created_at,
            'description' => trans('accounts.opening_balance') . ';' . $account->name,
            'reference' => 'opening-balance:' . $coa->id,
            'items' => [
                ['account_id' => $coa->id, 'debit' => $account->opening_balance],
                ['account_id' => $owner_contribution->id, 'credit' => $account->opening_balance],
            ],
        ];

        $this->dispatch(new CreateJournalEntry($request));
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $account
     * @return void
     */
    public function updated(Model $account)
    {
        $account_bank = AccountBank::where('bank_id', $account->id)->first();

        if (!$account_bank) {
            return;
        }

        $coa = $account_bank->account;

        $coa->update([
            'name' => $account->name,
            'code' => $coa->code,
            'type_id' => $coa->type_id,
            'enabled' => $account->enabled,
        ]);

        $journal_entry = Journal::where('reference', 'opening-balance:' . $coa->id)->first();

        if (is_null($journal_entry) && $account->opening_balance <= 0) {
            return;
        }

        if (is_null($journal_entry) && $account->opening_balance > 0) {
            $owner_contribution = Coa::code(setting('double-entry.accounts_owners_contribution'))->first();

            if (is_null($owner_contribution)) {
                return;
            }

            $request = [
                'company_id' => $account->company_id,
                'paid_at' => $account->created_at,
                'description' => trans('accounts.opening_balance') . ';' . $account->name,
                'reference' => 'opening-balance:' . $coa->id,
                'items' => [
                    ['account_id' => $coa->id, 'debit' => $account->opening_balance],
                    ['account_id' => $owner_contribution->id, 'credit' => $account->opening_balance],
                ],
            ];

            $this->dispatch(new CreateJournalEntry($request));

            return;
        }

        if ($account->opening_balance <= 0) {
            $this->dispatch(new DeleteJournalEntry($journal_entry));
            
            return;
        }

        if ($account->opening_balance > 0) {
            $request = ['items' => []];

            foreach ($journal_entry->ledgers as $ledger) {
                $postway = $ledger->debit == 0 ? 'credit' : 'debit';
                $request['items'][] = ['id' => $ledger->id, 'account_id' => $ledger->account_id, $postway => $account->opening_balance];
            }

            $this->dispatch(new UpdateJournalEntry($journal_entry, $request));
        }
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $account
     * @return void
     */
    public function deleted(Model $account)
    {
        $account_bank = AccountBank::where('bank_id', $account->id)->first();

        if (!$account_bank) {
            return;
        }

        $account_bank->account->delete();
    }
}
