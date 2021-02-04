<?php

namespace Modules\DoubleEntry\Observers\Banking;

use App\Abstracts\Observer;
use App\Models\Banking\Transaction as Model;
use App\Models\Setting\Category;
use Illuminate\Support\Str;
use Modules\DoubleEntry\Models\Account as Coa;
use Modules\DoubleEntry\Models\AccountBank;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Traits\Permissions;

class Transaction extends Observer
{
    use Permissions;
    
    //
    // Events
    //
    public function created(Model $transaction)
    {
        $this->createTransactionLedger($transaction);
    }

    public function updated(Model $transaction)
    {
        $this->updateTransactionLedger($transaction);
    }

    public function deleted(Model $transaction)
    {
        $this->deleteTransactionLedger($transaction);
    }

    //
    // Revenue/Payment
    //
    public function createTransactionLedger($transaction)
    {
        if ($this->isJournal($transaction) || $this->isTransfer($transaction)) {
            return;
        }

        $account_id = AccountBank::where('bank_id', $transaction->account_id)->pluck('account_id')->first();

        if (empty($account_id)) {
            return;
        }

        if ($this->isNotValidTransactionType($transaction)) {
            return;
        }

        $type = $this->getTransactionType($transaction);

        Ledger::create([
            'company_id' => $transaction->company_id,
            'account_id' => $account_id,
            'ledgerable_id' => $transaction->id,
            'ledgerable_type' => get_class($transaction),
            'issued_at' => $transaction->paid_at,
            'entry_type' => 'total',
            $type['total_field'] => $transaction->amount,
        ]);

        Ledger::create([
            'company_id' => $transaction->company_id,
            'account_id' => $type['account_id'],
            'ledgerable_id' => $transaction->id,
            'ledgerable_type' => get_class($transaction),
            'issued_at' => $transaction->paid_at,
            'entry_type' => 'item',
            $type['item_field'] => $transaction->amount,
        ]);
    }

    public function updateTransactionLedger($transaction)
    {
        if ($this->isJournal($transaction) || $this->isTransfer($transaction)) {
            return;
        }

        $ledger = Ledger::record($transaction->id, get_class($transaction))->where('entry_type', 'total')->first();

        if (empty($ledger)) {
            return;
        }

        $account_id = AccountBank::where('bank_id', $transaction->account_id)->pluck('account_id')->first();

        if (empty($account_id)) {
            return;
        }

        if ($this->isNotValidTransactionType($transaction)) {
            return;
        }

        $type = $this->getTransactionType($transaction);

        $ledger->update([
            'company_id' => $transaction->company_id,
            'account_id' => $account_id,
            'ledgerable_id' => $transaction->id,
            'ledgerable_type' => get_class($transaction),
            'issued_at' => $transaction->paid_at,
            'entry_type' => 'total',
            $type['total_field'] => $transaction->amount,
        ]);

        $ledger = Ledger::record($transaction->id, get_class($transaction))->where('entry_type', 'item')->first();

        if (empty($ledger)) {
            return;
        }

        $ledger->update([
            'company_id' => $transaction->company_id,
            'ledgerable_id' => $transaction->id,
            'ledgerable_type' => get_class($transaction),
            'issued_at' => $transaction->paid_at,
            'entry_type' => 'item',
            $type['item_field'] => $transaction->amount,
        ]);
    }

    public function deleteTransactionLedger($transaction)
    {
        if ($this->isJournal($transaction) || $this->isTransfer($transaction)) {
            return;
        }

        Ledger::record($transaction->id, get_class($transaction))->delete();
    }

    //
    // Helpers
    //
    protected function getTransactionType($transaction)
    {
        $transaction_type = [];

        if ($transaction->type == 'income') {
            if (is_null($transaction->document_id)) {
                $account_id = Coa::code(setting('double-entry.accounts_sales', 400))->pluck('id')->first();
            } else {
                $account_id = Coa::code(setting('double-entry.accounts_receivable', 120))->pluck('id')->first();
            }

            $transaction_type['total_field'] = 'debit';
            $transaction_type['item_field'] = 'credit';
            $transaction_type['account_id'] = $account_id;
        }

        if ($transaction->type == 'expense') {
            if (is_null($transaction->document_id)) {
                $account_id = Coa::code(setting('double-entry.accounts_expenses', 628))->pluck('id')->first();
            } else {
                $account_id = Coa::code(setting('double-entry.accounts_payable', 200))->pluck('id')->first();
            }

            $transaction_type['total_field'] = 'credit';
            $transaction_type['item_field'] = 'debit';
            $transaction_type['account_id'] = $account_id;
        }

        if (request()->has('de_account_id')) {
            $transaction_type['account_id'] = request('de_account_id');
        }

        return $transaction_type;
    }

    protected function isJournal($transaction)
    {
        if (empty($transaction->reference)) {
            return false;
        }

        if (!Str::contains($transaction->reference, 'journal-entry-ledger:')) {
            return false;
        }

        return true;
    }

    protected function isTransfer($transaction)
    {
        $transfer_id = (int) Category::disableCache()->where('type', 'other')->pluck('id')->first();

        if ($transaction->category_id != $transfer_id) {
            return false;
        }

        return true;
    }
}
