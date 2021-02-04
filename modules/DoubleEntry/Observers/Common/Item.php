<?php

namespace Modules\DoubleEntry\Observers\Common;

use App\Abstracts\Observer;
use App\Models\Common\Item as Model;
use Modules\DoubleEntry\Models\AccountItem;
use Modules\DoubleEntry\Traits\Module;

class Item extends Observer
{
    use Module;

    /**
     * Listen to the saved event.
     *
     * @param  Model  $item
     * @return void
     */
    public function saved(Model $item)
    {
        if ($this->isModuleDisabled()) {
            return;
        }

        $request = request();

        if (isset($request->de_income_account_id)) {
            AccountItem::updateOrCreate(
                ['company_id' => session('company_id'), 'item_id' => $item->id, 'type' => 'income'],
                ['account_id' => $request->de_income_account_id]
            );
        }

        if (isset($request->de_expense_account_id)) {
            AccountItem::updateOrCreate(
                ['company_id' => session('company_id'), 'item_id' => $item->id, 'type' => 'expense'],
                ['account_id' => $request->de_expense_account_id]
            );
        }
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $item
     * @return void
     */
    public function deleted(Model $item)
    {
        if ($this->isModuleDisabled()) {
            return;
        }

        AccountItem::where([
            'item_id' => $item->id,
        ])->delete();
    }
}
