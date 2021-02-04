<?php

namespace Modules\DoubleEntry\Jobs\Journal;

use App\Abstracts\Job;
use App\Traits\Relationships;
use Modules\DoubleEntry\Events\Journal\JournalDeleted;

class DeleteJournalEntry extends Job
{
    use Relationships;

    protected $journalEntry;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($journalEntry)
    {
        $this->journalEntry = $journalEntry;
    }

    /**
     * Execute the job.
     *
     * @return JournalEntry
     */
    public function handle()
    {
        \DB::transaction(function () {
            $this->deleteRelationships($this->journalEntry, ['ledgers']);
            $this->journalEntry->delete();
        });

        event(new JournalDeleted($this->journalEntry));

        return true;
    }
}
