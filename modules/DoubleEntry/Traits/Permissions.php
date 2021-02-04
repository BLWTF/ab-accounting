<?php

namespace Modules\DoubleEntry\Traits;

use App\Models\Document\Document;

trait Permissions
{
    protected function isNotValidDocumentType($type): bool
    {
        return !in_array($type, [Document::INVOICE_TYPE, Document::BILL_TYPE]);
    }
    
    protected function isNotValidTransactionType($transaction)
    {
        return !in_array($transaction->type, ['income', 'expense']);
    }
}
