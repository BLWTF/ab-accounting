<?php

namespace Modules\DoubleEntry\Widgets;

use App\Abstracts\Widget;
use App\Traits\Currencies;
use App\Utilities\Date;
use Modules\DoubleEntry\Models\Account as Coa;

class TotalIncomeByCoa extends Widget
{
    use Currencies;

    public $default_name = 'double-entry::widgets.total_income_by_coa';

    public $views = [
        'header' => 'partials.widgets.stats_header',
    ];

    public function show()
    {
        $current = $open = $overdue = 0;

        // income types
        $types = [13, 14, 15];

        Coa::with('ledgers')->inType($types)->enabled()->each(function ($coa) use (&$current, &$open, &$overdue) {

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

            $this->applyFilters($model, ['date_field' => 'issued_at'])->get()->each(function ($ledger) use (&$current, &$open, &$overdue) {
                $ledgerable = $ledger->ledgerable;

                switch ($ledgerable->getTable()) {
                    case 'document_items':
                        $today = Date::today()->toDateString();

                        if ($ledgerable->document->due_at > $today) {
                            $open += $ledger->credit;
                        } else {
                            $overdue += $ledger->credit;
                        }

                        break;
                    case 'transactions':
                        $current += $ledgerable->getAmountConvertedToDefault();

                        break;
                    case 'double_entry_journals':
                        $current += $ledger->credit;

                        break;
                    case 'document_totals':
                        $current += $this->convertToDefault($ledgerable->amount, $ledgerable->document->currency_code, $ledgerable->document->currency_rate);

                        break;
                }

            });

        });

        $grand = $current + $open + $overdue;

        $totals = [
            'grand' => money($grand, setting('default.currency'), true),
            'open' => money($open, setting('default.currency'), true),
            'overdue' => money($overdue, setting('default.currency'), true),
        ];

        return $this->view('widgets.total_income', compact('totals'));
    }
}
