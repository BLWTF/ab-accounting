<?php

namespace Modules\DoubleEntry\Http\ViewComposers;

use App\Models\Module\Module;
use Illuminate\View\View;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Type;
use Modules\DoubleEntry\Traits\Module as TraitsModule;

class ReceiptInput
{
    use TraitsModule;
    
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if ($this->isModuleDisabled()) {
            return;
        }

        if (is_null(Module::alias('receipt')->enabled()->first())) {
            return;
        }

        $values = [];

        $types = Type::whereHas('declass', function ($query) {
            $query->where('name', 'double-entry::classes.expenses');
        })->pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        $accounts = Account::with(['type'])->enabled()->orderBy('code')->get();

        foreach ($accounts as $account) {
            if (!isset($types[$account->type_id])) {
                continue;
            }
            $values[$types[$account->type_id]][$account->id] = $account->code . ' - ' . trans($account->name);
        }

        ksort($values);

        $name = 'de_account_id';
        $text = trans_choice('general.accounts', 1);
        $attributes = ['required' => null];
        $col = 'col-md-6';

        $view->getFactory()->startPush('tax_amount_input_end', view('double-entry::partials.input_account_group', compact('name', 'text', 'values', 'attributes', 'col')));
    }
}
