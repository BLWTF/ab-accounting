<?php

namespace Modules\DoubleEntry\Observers\Document;

use App\Abstracts\Observer;
use App\Models\Document\DocumentTotal as Model;
use App\Traits\Jobs;
use Modules\DoubleEntry\Jobs\Ledger\CreateLedger;
use Modules\DoubleEntry\Jobs\Ledger\DeleteLedger;
use Modules\DoubleEntry\Jobs\Ledger\UpdateLedger;
use Modules\DoubleEntry\Models\Account as Coa;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Traits\Permissions;

class DocumentTotal extends Observer
{
    use Jobs, Permissions;

    /**
     * Listen to the created event.
     *
     * @param  Model  $document_total
     * @return void
     */
    public function created(Model $document_total)
    {
        if ($this->isNotValidDocumentType($document_total->document->type)) {
            return;
        }

        if ($document_total->code != 'discount') {
            return;
        }

        $request = $this->getDocumentTotalBaseRequest($document_total);

        $request = $this->appendDocumentTotalSpecificFields($request, $document_total);

        $this->dispatch(new CreateLedger($request));
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $document_total
     * @return void
     */
    public function updated(Model $document_total)
    {
        if ($this->isNotValidDocumentType($document_total->document->type)) {
            return;
        }

        $ledger = Ledger::record($document_total->id, get_class($document_total))->first();

        if (is_null($ledger)) {
            return;
        }

        $request = $this->getDocumentTotalBaseRequest($document_total);

        $request = $this->appendDocumentTotalSpecificFields($request, $document_total);

        $this->dispatch(new UpdateLedger($ledger, $request));
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $document_total
     * @return void
     */
    public function deleted(Model $document_total)
    {
        if ($this->isNotValidDocumentType($document_total->document->type)) {
            return;
        }

        $ledger = Ledger::record($document_total->id, get_class($document_total))->first();

        if (is_null($ledger)) {
            return;
        }

        $this->dispatch(new DeleteLedger($ledger));
    }

    private function getDocumentTotalBaseRequest($document_total)
    {
        return [
            'company_id' => $document_total->company_id,
            'ledgerable_id' => $document_total->id,
            'ledgerable_type' => get_class($document_total),
            'issued_at' => $document_total->document->issued_at,
            'entry_type' => 'discount',
        ];
    }

    private function appendDocumentTotalSpecificFields($request, $document_total)
    {
        if ($document_total->document->type == 'invoice') {
            $request['account_id'] = Coa::code(setting('double-entry.accounts_sales_discount', 825))->pluck('id')->first();
            $request['debit'] = $document_total->amount;
        }

        if ($document_total->document->type == 'bill') {
            $request['account_id'] = Coa::code(setting('double-entry.accounts_purchase_discount', 475))->pluck('id')->first();
            $request['credit'] = $document_total->amount;
        }

        return $request;
    }
}
