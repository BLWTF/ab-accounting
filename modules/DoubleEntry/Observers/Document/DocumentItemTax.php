<?php

namespace Modules\DoubleEntry\Observers\Document;

use App\Abstracts\Observer;
use App\Models\Document\DocumentItemTax as Model;
use App\Traits\Jobs;
use Modules\DoubleEntry\Jobs\Ledger\CreateLedger;
use Modules\DoubleEntry\Jobs\Ledger\DeleteLedger;
use Modules\DoubleEntry\Models\AccountTax;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Traits\Permissions;

class DocumentItemTax extends Observer
{
    use Jobs, Permissions;

    /**
     * Listen to the created event.
     *
     * @param  Model  $document_item_tax
     * @return void
     */
    public function created(Model $document_item_tax)
    {
        if ($this->isNotValidDocumentType($document_item_tax->document->type)) {
            return;
        }

        $account_id = AccountTax::where('tax_id', $document_item_tax->tax_id)->pluck('account_id')->first();

        if (is_null($account_id)) {
            return;
        }

        $request = $this->getDocumentItemTaxBaseRequest($document_item_tax);

        $request['account_id'] = $account_id;

        $this->dispatch(new CreateLedger($request));
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $document_item_tax
     * @return void
     */
    public function deleted(Model $document_item_tax)
    {
        if ($this->isNotValidDocumentType($document_item_tax->document->type)) {
            return;
        }

        $ledger = Ledger::record($document_item_tax->id, get_class($document_item_tax))->first();

        if (is_null($ledger)) {
            return;
        }

        $this->dispatch(new DeleteLedger($ledger));
    }
    
    private function getDocumentItemTaxBaseRequest($document_item_tax)
    {
        if ($document_item_tax->document->type == 'invoice') {
            $label = 'credit';

            if ($document_item_tax->tax->type == 'withholding') {
                $label = 'debit';
            }
        }

        if ($document_item_tax->document->type == 'bill') {
            $label = 'debit';

            if ($document_item_tax->tax->type == 'withholding') {
                $label = 'credit';
            }
        }

        return [
            'company_id' => $document_item_tax->company_id,
            'ledgerable_id' => $document_item_tax->id,
            'ledgerable_type' => get_class($document_item_tax),
            'issued_at' => $document_item_tax->document->issued_at,
            'entry_type' => 'item',
            $label => $document_item_tax->amount,
        ];
    }
}
