<?php

namespace Modules\DoubleEntry\Observers\Setting;

use App\Abstracts\Observer;
use App\Models\Setting\Tax as Model;
use Modules\DoubleEntry\Models\Account as Coa;
use Modules\DoubleEntry\Models\AccountTax;
use Modules\DoubleEntry\Traits\Module;

class Tax extends Observer
{
    use Module;

    /**
     * Listen to the created event.
     *
     * @param  Model  $tax
     * @return void
     */
    public function created(Model $tax)
    {
        if ($this->isModuleDisabled()) {
            return;
        }

        $coa = Coa::create([
            'company_id' => $tax->company_id,
            'type_id' => setting('double-entry.types_tax', 17),
            'code' => Coa::max('code') + 1,
            'name' => $tax->name,
            'enabled' => $tax->enabled,
        ]);

        AccountTax::create([
            'company_id' => $tax->company_id,
            'account_id' => $coa->id,
            'tax_id' => $tax->id,
        ]);
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $tax
     * @return void
     */
    public function updated(Model $tax)
    {
        $account_tax = AccountTax::where('tax_id', $tax->id)->first();

        if (!$account_tax) {
            return;
        }

        $coa = $account_tax->account;

        $coa->update([
            'name' => $tax->name,
            'code' => $coa->code,
            'type_id' => $coa->type_id,
            'enabled' => $tax->enabled,
        ]);
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $tax
     * @return void
     */
    public function deleted(Model $tax)
    {
        $account_tax = AccountTax::where('tax_id', $tax->id)->first();

        if (!$account_tax) {
            return;
        }

        $account_tax->account->delete();
    }
}
