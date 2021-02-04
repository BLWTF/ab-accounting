<?php

namespace Modules\DoubleEntry\Providers;

use App\Models\Banking\Account;
use App\Models\Banking\Transaction;
use App\Models\Banking\Transfer;
use App\Models\Common\Item;
use App\Models\Document\Document;
use App\Models\Document\DocumentItem;
use App\Models\Document\DocumentItemTax;
use App\Models\Document\DocumentTotal;
use App\Models\Setting\Tax;
use Illuminate\Support\ServiceProvider;

class Observer extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        Account::observe('Modules\DoubleEntry\Observers\Banking\Account');
        Document::observe('Modules\DoubleEntry\Observers\Document\Document');
        DocumentItem::observe('Modules\DoubleEntry\Observers\Document\DocumentItem');
        DocumentItemTax::observe('Modules\DoubleEntry\Observers\Document\DocumentItemTax');
        DocumentTotal::observe('Modules\DoubleEntry\Observers\Document\DocumentTotal');
        Tax::observe('Modules\DoubleEntry\Observers\Setting\Tax');
        Transaction::observe('Modules\DoubleEntry\Observers\Banking\Transaction');
        Transfer::observe('Modules\DoubleEntry\Observers\Banking\Transfer');
        Item::observe('Modules\DoubleEntry\Observers\Common\Item');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
