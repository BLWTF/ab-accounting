<?php

namespace Modules\DoubleEntry\Reports;

use App\Abstracts\Report;
use Modules\DoubleEntry\Models\DEClass;
use stdClass;

class BalanceSheet extends Report
{
    public $default_name = 'double-entry::general.balance_sheet';

    public $category = 'general.accounting';

    public $icon = 'fa fa-balance-scale';

    public $total_liabilities_equity = 0;

    public function getGrandTotal()
    {
        return trans('general.na');
    }

    public function setViews()
    {
        parent::setViews();
        $this->views['content'] = 'double-entry::balance_sheet.content';
    }

    public function setData()
    {
        $accounts = [];
        $liabilities = 0;

        $classes = DEClass::whereNotIn('name', ['double-entry::classes.income', 'double-entry::classes.expenses'])
        ->with(['types', 'types.accounts' => function ($query) {
            $query->has('ledgers');
        }])->get();

        foreach ($classes as $class) {
            $class->total = 0;

            foreach ($class->types as $type) {
                $type->total = 0;

                if ($type->name == 'double-entry::types.equity') {
                    $account = $this->calculateCurrentYearEarnings();
                    $accounts[$type->id][] = $account;
                    $type->total += $account->balance;
                    $class->total += $account->balance;
                }

                foreach ($type->accounts as $account) {
                    $balance = $account->balance;

                    if ($type->name == 'double-entry::types.depreciation' || $account->code == '310' || $account->code == '300' 
                    || $account->code == '320' || $class->name == 'double-entry::classes.liabilities') {
                        $balance = $balance * -1;
                    }

                    $account->de_balance = $balance;
                    $type->total += $balance;
                    $class->total += $balance;

                    $accounts[$type->id][] = $account;
                }
            }

            if ($class->name == 'double-entry::classes.liabilities') {
                $liabilities = $class->total;
            }

            if ($class->name == 'double-entry::classes.equity') {
                $this->total_liabilities_equity = $liabilities + $class->total;
            }
        }

        $this->de_classes = $classes;
        $this->de_accounts = $accounts;
    }

    public function getFields()
    {
        return [];
    }

    protected function calculateCurrentYearEarnings()
    {
        $income = DEClass::where('name', 'double-entry::classes.income')->first()->accounts()->has('ledgers')->get()->sum(function ($account) {
            return $account->balance;
        });

        $expense = DEClass::where('name', 'double-entry::classes.expenses')->first()->accounts()->has('ledgers')->get()->sum(function ($account) {
            return $account->balance;
        });

        $earning = new stdClass;
        $earning->name = trans('double-entry::general.current_year_earnings');
        $earning->balance = abs($income) - $expense;

        return $earning;
    }
}
