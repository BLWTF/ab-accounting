<?php

namespace Modules\DoubleEntry\Listeners;

use App\Abstracts\Listeners\Report as Listener;
use App\Events\Report\DataLoaded;
use Modules\DoubleEntry\Models\DEClass;

class AddJournalDataToCoreReports extends Listener
{
    public $classes = [
        'App\Reports\IncomeSummary',
        'App\Reports\ExpenseSummary',
        'App\Reports\IncomeExpenseSummary',
        'App\Reports\ProfitLoss',
    ];

    /**
     * Handle the event.
     *
     * @param DataLoaded $event
     * @return void
     */
    public function handle(DataLoaded $event)
    {
        if ($this->skipThisClass($event)) {
            return;
        }

        $report = $event->class;

        switch (get_class($report)) {
            case 'App\Reports\IncomeSummary':
                $journal_entries_income = $this->getIncomeJournals();
                $report->setTotals($journal_entries_income, 'issued_at', false, 'default', false);

                break;
            case 'App\Reports\ExpenseSummary':
                $journal_entries_expense = $this->getExpenseJournals();
                $report->setTotals($journal_entries_expense, 'issued_at', false, 'default', false);

                break;
            case 'App\Reports\IncomeExpenseSummary':
                $journal_entries_income = $this->getIncomeJournals();
                $journal_entries_expense = $this->getExpenseJournals();
                $journals = $journal_entries_income;

                foreach ($journal_entries_expense as $journal_expense) {
                    $hasJournal = false;

                    foreach ($journals as $journal) {
                        if ($journal_expense->is($journal)) {
                            $hasJournal = true;
                        }

                    }

                    if (!$hasJournal) {
                        array_push($journals, $journal_expense);
                    }
                }

                $report->setTotals($journals, 'issued_at', false, 'default', false);
                break;
            case 'App\Reports\ProfitLoss':
                $journal_entries_income = $this->getIncomeJournals();
                $report->setTotals($journal_entries_income, 'issued_at', false, 'default', false);

                $journal_entries_expense = $this->getExpenseJournals();
                $report->setTotals($journal_entries_expense, 'issued_at', false, 'default', false);
                break;

            default:

                break;
        }

    }

    protected function getIncomeJournals()
    {
        $journals = [];

        DEClass::where('name', 'double-entry::classes.income')->with(['accounts'])->each(function ($de_class) use (&$journals) {

            $de_class->accounts()->enabled()->each(function ($account) use (&$journals) {

                $account->ledgers()->where('ledgerable_type', 'Modules\DoubleEntry\Models\Journal')->each(function ($ledger) use (&$journals) {
                    $hasJournal = false;
                    $journal = $ledger->ledgerable;
                    $journal->type = 'income';

                    foreach ($journals as $journal_entry) {
                        if ($journal->is($journal_entry)) {
                            $hasJournal = true;
                            break;
                        }
                    }

                    if (!$hasJournal) {
                        array_push($journals, $journal);
                    }
                });

            });

        });

        return $journals;
    }

    protected function getExpenseJournals()
    {
        $journals = [];

        DEClass::where('name', 'double-entry::classes.expenses')->with(['accounts'])->each(function ($de_class) use (&$journals) {

            $de_class->accounts()->enabled()->each(function ($account) use (&$journals) {

                $account->ledgers()->where('ledgerable_type', 'Modules\DoubleEntry\Models\Journal')->each(function ($ledger) use (&$journals) {
                    $hasJournal = false;
                    $journal = $ledger->ledgerable;
                    $journal->type = 'expense';

                    foreach ($journals as $journal_entry) {
                        if ($journal->is($journal_entry)) {
                            $hasJournal = true;
                            break;
                        }
                    }

                    if (!$hasJournal) {
                        array_push($journals, $journal);
                    }
                });

            });

        });

        return $journals;
    }
}
