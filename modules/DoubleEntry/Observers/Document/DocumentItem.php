<?php

namespace Modules\DoubleEntry\Observers\Document;

use App\Abstracts\Observer;
use App\Models\Document\DocumentItem as Model;
use App\Traits\Jobs;
use Modules\DoubleEntry\Jobs\Ledger\CreateLedger;
use Modules\DoubleEntry\Jobs\Ledger\DeleteLedger;
use Modules\DoubleEntry\Models\Account as Coa;
use Modules\DoubleEntry\Models\Ledger;
use Modules\DoubleEntry\Traits\Permissions;

class DocumentItem extends Observer
{
    use Jobs, Permissions;

    /**
     * Listen to the created event.
     *
     * @param  Model  $document_item
     * @return void
     */
    public function created(Model $document_item)
    {
        if ($this->isNotValidDocumentType($document_item->document->type)) {
            return;
        }

        $request = $this->getDocumentItemBaseRequest($document_item);

        $request = $this->appendDocumentItemSpecificFields($request, $document_item);

        $this->dispatch(new CreateLedger($request));
    }

    /**
     * Listen to the deleted event.
     *
     * @param  Model  $document_item
     * @return void
     */
    public function deleted(Model $document_item)
    {
        if ($this->isNotValidDocumentType($document_item->document->type)) {
            return;
        }

        $ledger = Ledger::record($document_item->id, get_class($document_item))->first();

        if (is_null($ledger)) {
            return;
        }

        $this->dispatch(new DeleteLedger($ledger));
    }

    private function getDocumentItemBaseRequest($document_item)
    {
        return [
            'company_id' => $document_item->company_id,
            'ledgerable_id' => $document_item->id,
            'ledgerable_type' => get_class($document_item),
            'issued_at' => $document_item->document->issued_at,
            'entry_type' => 'item',
        ];
    }

    private function appendDocumentItemSpecificFields($request, $document_item)
    {
        $account_id = null;

        $r_items = request()->input('items');

        if (is_array($r_items)) {
            foreach ($r_items as $r_item) {
                if ($r_item['name'] != $document_item->name) {
                    continue;
                }

                $account_id = isset($r_item['de_account_id']) ? $r_item['de_account_id'] : '';

                break;
            }
        }

        $request['account_id'] = $account_id;

        if ($document_item->document->type == 'invoice') {
            $request['credit'] = $document_item->total;

            if (empty($account_id)) {
                $request['account_id'] = Coa::code(setting('double-entry.accounts_sales', 400))->pluck('id')->first();
            }
        }

        if ($document_item->document->type == 'bill') {
            $request['debit'] = $document_item->total;

            if (empty($account_id)) {
                $request['account_id'] = Coa::code(setting('double-entry.accounts_expenses', 628))->pluck('id')->first();
            }
        }

        return $request;
    }
}
