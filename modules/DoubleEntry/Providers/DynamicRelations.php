<?php

namespace Modules\DoubleEntry\Providers;

use App\Models\Banking\Transaction;
use App\Models\Document\Document;
use App\Models\Document\DocumentItem;
use App\Models\Document\DocumentItemTax;
use App\Models\Document\DocumentTotal;
use Illuminate\Support\ServiceProvider;
use Modules\DoubleEntry\Models\Journal;

class DynamicRelations extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Document::resolveRelationUsing('de_ledger', function ($document) {
            return $document->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        DocumentItem::resolveRelationUsing('de_ledger', function ($documentItem) {
            return $documentItem->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        DocumentItemTax::resolveRelationUsing('de_ledger', function ($documentItemTax) {
            return $documentItemTax->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        DocumentTotal::resolveRelationUsing('de_ledger', function ($documentTotal) {
            return $documentTotal->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        Transaction::resolveRelationUsing('de_ledger', function ($transaction) {
            return $transaction->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });

        Journal::resolveRelationUsing('de_ledger', function ($journal) {
            return $journal->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
        });
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
