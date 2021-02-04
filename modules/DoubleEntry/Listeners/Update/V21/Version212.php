<?php

namespace Modules\DoubleEntry\Listeners\Update\V21;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\File;

class Version212 extends Listener
{
    const ALIAS = 'double-entry';

    const VERSION = '2.1.2';

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        if ($this->skipThisUpdate($event)) {
            return;
        }

        $this->deleteOldFiles();
    }

    protected function deleteOldFiles()
    {
        $files = [
            'Http/ViewComposers/BillInput.php',
            'Http/ViewComposers/BillTable.php',
            'Http/ViewComposers/InvoiceInput.php',
            'Http/ViewComposers/InvoiceTable.php',
            'Listeners/Update/Version200.php',
            'Listeners/Update/Version205.php',
            'Listeners/Update/Version207.php',
            'Listeners/Update/Version2014.php',
            'Listeners/Update/Version2019.php',
            'Listeners/Update/Version211.php',
            'Observers/Purchase/Bill.php',
            'Observers/Purchase/BillItem.php',
            'Observers/Purchase/BillItemTax.php',
            'Observers/Purchase/BillTotal.php',
            'Observers/Sale/Invoice.php',
            'Observers/Sale/InvoiceItem.php',
            'Observers/Sale/InvoiceItemTax.php',
            'Observers/Sale/InvoiceTotal.php',
            'Observers/DoubleEntry/JournalLedger.php',
            'Jobs/CreateAccount.php',
            'Jobs/UpdateAccount.php',
            'Providers/Macro.php',
        ];

        foreach ($files as $file) {
            File::delete(base_path('modules/DoubleEntry/' . $file));
        }
    }
}
