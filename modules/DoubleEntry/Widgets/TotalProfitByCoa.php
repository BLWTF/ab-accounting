<?php

namespace Modules\DoubleEntry\Widgets;

use App\Abstracts\Widget;
use App\Traits\Currencies;
use App\Utilities\Date;
use Modules\DoubleEntry\Models\Account as Coa;

class TotalProfitByCoa extends Widget
{
    use Currencies;

    public $default_name = 'double-entry::widgets.total_profit_by_coa';

    public $views = [
        'header' => 'partials.widgets.stats_header',
    ];

    public function show()
    {
        $current_income = $open_invoice = $overdue_invoice = 0;
        $current_expenses = $open_bill = $overdue_bill = 0;

        // income types
        $types_incomes = [13, 14, 15];

        Coa::with('ledgers')->inType($types_incomes)->enabled()->each(function ($coa) use (&$current_income, &$open_invoice, &$overdue_invoice) {

            $model = $coa->ledgers()
                ->whereNotNull('credit')
                ->whereHasMorph('ledgerable', [
                    'App\Models\Banking\Transaction',
                    'Modules\DoubleEntry\Models\Journal',
                    'App\Models\Document\DocumentItem',
                    'App\Models\Document\DocumentTotal',
                ], function ($query, $type) {
                    if ($type == 'App\Models\Banking\Transaction') {
                        $query->whereNull('document_id');
                    }
                    
                    if ($type == 'App\Models\Document\DocumentItem') {
                        $query->whereHas('document', function ($query) {
                            $query->invoice()->accrued();
                        });
                    }

                    if ($type == 'App\Models\Document\DocumentTotal') {
                        $query->whereHas('document', function ($query) {
                            $query->bill()->accrued();
                        });
                    }
                });

            $this->applyFilters($model, ['date_field' => 'issued_at'])->get()->each(function ($ledger) use (&$current_income, &$open_invoice, &$overdue_invoice) {
                $ledgerable = $ledger->ledgerable;

                switch ($ledgerable->getTable()) {
                    case 'document_items':
                        $today = Date::today()->toDateString();

                        if ($ledgerable->document->due_at > $today) {
                            $open_invoice += $ledger->credit;
                        } else {
                            $overdue_invoice += $ledger->credit;
                        }

                        break;
                    case 'transactions':
                        $current_income += $ledgerable->getAmountConvertedToDefault();

                        break;
                    case 'double_entry_journals':
                        $current_income += $ledger->credit;

                        break;
                    case 'document_totals':
                        $current_income += $this->convertToDefault($ledgerable->amount, $ledgerable->document->currency_code, $ledgerable->document->currency_rate);

                        break;
                }

            });

        });

        // expense types
        $types_expenses = [11, 12];

        Coa::with('ledgers')->inType($types_expenses)->enabled()->each(function ($coa) use (&$current_expenses, &$open_bill, &$overdue_bill) {

            $model = $coa->ledgers()
                ->whereNotNull('debit')
                ->whereHasMorph('ledgerable', [
                    'App\Models\Banking\Transaction',
                    'Modules\DoubleEntry\Models\Journal',
                    'App\Models\Document\DocumentItem',
                    'App\Models\Document\DocumentTotal',
                ], function ($query, $type) {
                    if ($type == 'App\Models\Banking\Transaction') {
                        $query->whereNull('document_id');
                    }
                                        
                    if ($type == 'App\Models\Document\DocumentItem') {
                        $query->whereHas('document', function ($query) {
                            $query->bill()->accrued();
                        });
                    }

                    if ($type == 'App\Models\Document\DocumentTotal') {
                        $query->whereHas('document', function ($query) {
                            $query->invoice()->accrued();
                        });
                    }
                });

            $this->applyFilters($model, ['date_field' => 'issued_at'])->get()->each(function ($ledger) use (&$current_expenses, &$open_bill, &$overdue_bill) {
                $ledgerable = $ledger->ledgerable;

                switch ($ledgerable->getTable()) {
                    case 'document_items':
                        $today = Date::today()->toDateString();

                        if ($ledgerable->document->due_at > $today) {
                            $open_bill += $ledger->debit;
                        } else {
                            $overdue_bill += $ledger->debit;
                        }

                        break;
                    case 'transactions':
                        $current_expenses += $ledgerable->getAmountConvertedToDefault();

                        break;
                    case 'double_entry_journals':
                        $current_expenses += $ledger->debit;

                        break;
                    case 'document_totals':
                        $current_expenses += $this->convertToDefault($ledgerable->amount, $ledgerable->document->currency_code, $ledgerable->document->currency_rate);

                        break;
                }

            });

        });

        $current = $current_income - $current_expenses;
        $open = $open_invoice - $open_bill;
        $overdue = $overdue_invoice - $overdue_bill;

        $grand = $current + $open + $overdue;

        $totals = [
            'grand' => money($grand, setting('default.currency'), true),
            'open' => money($open, setting('default.currency'), true),
            'overdue' => money($overdue, setting('default.currency'), true),
        ];

        return $this->view('widgets.total_profit', compact('totals'));
    }
}
