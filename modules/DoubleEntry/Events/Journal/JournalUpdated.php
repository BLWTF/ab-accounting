<?php

namespace Modules\DoubleEntry\Events\Journal;

use Illuminate\Queue\SerializesModels;

class JournalUpdated
{
    use SerializesModels;

    public $journal;

    public $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($journal, $request)
    {
        $this->journal = $journal;
        $this->request = $request;
    }
}
