<?php

namespace Modules\DoubleEntry\Observers\Document;

use App\Abstracts\Observer;
use App\Models\Document\Document as Model;
use App\Traits\Jobs;
use Modules\DoubleEntry\Jobs\Ledger\CreateLedger;
use Modules\DoubleEntry\Jobs\Ledger\DeleteLedger;
use Modules\DoubleEntry\Jobs\Ledger\UpdateLedger;
use Modules\DoubleEntry\Models\Account as Coa;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Traits\Permissions;

class Document extends Observer
{
    use Jobs, Permissions;

    /**
     * Listen to the created event.
     *
     * @param  Model  $document
     * @return void
     */
    public function created(Model $document)
    {
        if ($this->isNotValidDocumentType($document->type)) {
            return;
        }

        $request = $this->getDocumentBaseRequest($document);

        $request = $this->appendDocumentSpecificFields($request, $document);

        $this->dispatch(new CreateLedger($request));
    }

    /**
     * Listen to the created event.
     *
     * @param  Model  $document
     * @return void
     */
    public function updated(Model $document)
    {
        if ($this->isNotValidDocumentType($document->type)) {
            return;
        }

        $ledger = Ledger::record($document->id, get_class($document))->first();

        if (is_null($ledger)) {
            return;
        }

        $request = $this->getDocumentBaseRequest($document);

        $request = $this->appendDocumentSpecificFields($request, $document);

        $this->dispatch(new UpdateLedger($ledger, $request));
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $document
     * @return void
     */
    public function deleted(Model $document)
    {
        if ($this->isNotValidDocumentType($document->type)) {
            return;
        }
        
        $ledger = Ledger::record($document->id, get_class($document))->first();

        if (is_null($ledger)) {
            return;
        }

        $this->dispatch(new DeleteLedger($ledger));
    }

    private function getDocumentBaseRequest($document)
    {
        return [
            'company_id' => $document->company_id,
            'ledgerable_id' => $document->id,
            'ledgerable_type' => get_class($document),
            'issued_at' => $document->issued_at,
            'entry_type' => 'total',
        ];
    }

    private function appendDocumentSpecificFields($request, $document)
    {
        if ($document->type == 'invoice') {
            $request['account_id'] = Coa::code(setting('double-entry.accounts_receivable', 120))->pluck('id')->first();
            $request['debit'] = $document->amount;
        }

        if ($document->type == 'bill') {
            $request['account_id'] = Coa::code(setting('double-entry.accounts_payable', 200))->pluck('id')->first();
            $request['credit'] = $document->amount;
        }

        return $request;
    }
}
