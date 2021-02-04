<?php

namespace Modules\DoubleEntry\Listeners;

use Modules\DoubleEntry\Models\Ledger;

class DocumentCloned
{
    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle($clone, $original)
    {
        $original_items = $original->items;
        $clone_items = $clone->items;

        for ($i = 0; $i < count($original_items); $i++) { 
            Ledger::record($clone_items[$i]->id, 'App\Models\Document\DocumentItem')->update([
                'account_id' => Ledger::record($original_items[$i]->id, 'App\Models\Document\DocumentItem')->value('account_id')
            ]);
        }
    }
}
