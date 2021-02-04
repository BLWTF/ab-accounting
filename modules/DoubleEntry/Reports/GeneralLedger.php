<?php

namespace Modules\DoubleEntry\Reports;

use App\Abstracts\Report;
use Modules\DoubleEntry\Models\Account as Coa;
use Modules\DoubleEntry\Models\DEClass;

class GeneralLedger extends Report
{
    public $default_name = 'double-entry::general.general_ledger';

    public $category = 'general.accounting';

    public $icon = 'fa fa-balance-scale';

    public function getGrandTotal()
    {
        return trans('general.na');
    }

    public function setViews()
    {
        parent::setViews();
        $this->views['show'] = 'double-entry::general_ledger.show';
        $this->views['content'] = 'double-entry::general_ledger.content';
    }

    public function setData()
    {
        $model = $this->applyFilters(Coa::with(['type.declass', 'ledgers' => function ($query) {
            $query->orderBy('issued_at', 'asc');
        }, 'ledgers.ledgerable'])->has('ledgers'));

        $accounts = $model->get()->each(function ($account) {
            $account->name = trans($account->name);
            $account->class_id = $account->type->declass->id;
        });

        $this->de_classes = DEClass::with(['types'])->orderBy('id')->get();
        $this->de_accounts = $accounts;
    }

    public function getUrl($action = 'print')
    {
        $print_url = 'common/reports/' . $this->model->id . '/' . $action;

        collect(request('de_accounts'))->each(function ($item) use (&$print_url) {
            $print_url .= '?de_accounts[]=' . $item;
        });

        return $print_url;
    }
    
    public function getFields()
    {
        return [];
    }
}
