<?php

namespace Modules\DoubleEntry\Widgets;

use App\Abstracts\Widget;
use App\Traits\Currencies;
use Modules\DoubleEntry\Models\Ledger;

class LatestExpensesByCoa extends Widget
{
    use Currencies;

    public $default_name = 'double-entry::widgets.latest_expenses';

    public function show()
    {
        // expense types
        $types = [11, 12];

        $model = Ledger::whereHas('account', function ($query) use ($types) {
            $query->whereIn('type_id', $types);
        })
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
            })
            ->orderBy('issued_at', 'desc')
            ->take(5);

        $ledgers = $this->applyFilters($model, ['date_field' => 'issued_at'])->get()->transform(function ($ledger) {
            $ledgerable = $ledger->ledgerable;

            switch ($ledgerable->getTable()) {
                case 'document_items':
                case 'double_entry_journals':
                    $ledger->amount = $ledger->debit;

                    break;
                case 'transactions':
                    $ledger->amount += $ledgerable->getAmountConvertedToDefault();

                    break;
                case 'document_totals':
                    $ledger->amount += $this->convertToDefault($ledgerable->amount, $ledgerable->document->currency_code, $ledgerable->document->currency_rate);

                    break;
            }

            return $ledger;
        })->all();

        return $this->view('double-entry::widgets.latest', compact('ledgers'));
    }
}
