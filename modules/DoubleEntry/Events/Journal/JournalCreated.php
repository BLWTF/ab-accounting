<?php

namespace Modules\DoubleEntry\Events\Journal;

use Illuminate\Queue\SerializesModels;

class JournalCreated
{
    use SerializesModels;

    public $journal;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($journal)
    {
        $this->journal = $journal;
    }
}
