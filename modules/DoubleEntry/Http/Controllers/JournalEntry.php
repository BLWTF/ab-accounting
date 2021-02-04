<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Abstracts\Http\Controller;
use App\Models\Setting\Currency;
use App\Traits\DateTime;
use Illuminate\Http\Request as ItemRequest;
use Modules\DoubleEntry\Http\Requests\Journal as Request;
use Modules\DoubleEntry\Jobs\Journal\CreateJournalEntry;
use Modules\DoubleEntry\Jobs\Journal\DeleteJournalEntry;
use Modules\DoubleEntry\Jobs\Journal\UpdateJournalEntry;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\Journal;
use Modules\DoubleEntry\Models\Type;

class JournalEntry extends Controller
{
    use DateTime;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $journals = Journal::collect(['paid_at' => 'desc']);

        return view('double-entry::journal_entry.index', compact('journals'));
    }

    /**
     * Show the form for viewing the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        return redirect()->route('journal-entry.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $accounts = [];

        $types = Type::pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        $all_accounts = Account::with(['type'])->enabled()->orderBy('code')->get();

        foreach ($all_accounts as $account) {
            if (!isset($types[$account->type_id])) {
                continue;
            }

            $accounts[$types[$account->type_id]][$account->id] = $account->code . ' - ' . trans($account->name);
        }

        ksort($accounts);

        $currency = Currency::code(setting('default.currency', 'USD'))->first();

        return view('double-entry::journal_entry.create', compact('accounts', 'currency'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->dispatch(new CreateJournalEntry($request));

        $message = trans('messages.success.added', ['type' => trans('double-entry::general.journal_entry')]);

        flash($message)->success();

        return response()->json([
            'success' => true,
            'error' => false,
            'data' => [],
            'redirect' => route('journal-entry.index'),
            'message' => $message,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Journal  $journal_entry
     *
     * @return Response
     */
    public function edit(Journal $journal_entry)
    {
        $journal = $journal_entry;

        $accounts = [];

        $types = Type::pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        $all_accounts = Account::with(['type'])->enabled()->orderBy('code')->get();

        foreach ($all_accounts as $account) {
            if (!isset($types[$account->type_id])) {
                continue;
            }

            $accounts[$types[$account->type_id]][$account->id] = $account->code . ' - ' . trans($account->name);
        }

        ksort($accounts);

        foreach ($journal->ledgers as $ledger) {
            if (!empty($ledger->debit)) {
                $journal->debit_account_id = $ledger->account_id;
                $journal->debit_amount = $ledger->debit;
            } else {
                $journal->credit_account_id = $ledger->account_id;
                $journal->credit_amount = $ledger->credit;
            }
        }

        $currency = Currency::code(setting('default.currency', 'USD'))->first();

        return view('double-entry::journal_entry.edit', compact('journal', 'accounts', 'currency'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Journal  $journal_entry
     * @param  Request  $request
     *
     * @return Response
     */
    public function update(Journal $journal_entry, Request $request)
    {
        $this->dispatch(new UpdateJournalEntry($journal_entry, $request));

        $message = trans('messages.success.updated', ['type' => trans('double-entry::general.journal_entry')]);

        flash($message)->success();

        return response()->json([
            'success' => true,
            'error' => false,
            'data' => [],
            'redirect' => route('journal-entry.index'),
            'message' => $message,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Journal  $journal_entry
     *
     * @return Response
     */
    public function destroy(Journal $journal_entry)
    {
        $this->dispatch(new DeleteJournalEntry($journal_entry));

        $message = trans('messages.success.deleted', ['type' => trans('double-entry::general.journal_entry')]);

        flash($message)->success();

        return response()->json([
            'success' => true,
            'error' => false,
            'data' => $message,
            'message' => '',
            'redirect' => route('journal-entry.index'),
        ]);
    }

    public function addItem(ItemRequest $request)
    {
        if ($request['item_row']) {
            $accounts = [];

            $types = Type::pluck('name', 'id')->map(function ($name) {
                return trans($name);
            })->toArray();

            $all_accounts = Account::with(['type'])->enabled()->orderBy('code')->get();

            foreach ($all_accounts as $account) {
                if (!isset($types[$account->type_id])) {
                    continue;
                }

                $accounts[$types[$account->type_id]][$account->id] = $account->code . ' - ' . trans($account->name);
            }

            ksort($accounts);

            $item_row = $request['item_row'];

            $currency = Currency::where('code', '=', setting('default.currency', 'USD'))->first();

            // it should be integer for amount mask
            $currency->precision = (int) $currency->precision;

            $html = view('double-entry::journal-entry.item', compact('item_row', 'accounts', 'currency'))->render();

            return response()->json([
                'success' => true,
                'error' => false,
                'data' => [
                    'currency' => $currency,
                ],
                'message' => 'null',
                'html' => $html,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => true,
            'data' => 'null',
            'message' => trans('issue'),
            'html' => 'null',
        ]);
    }

    public function totalItem(ItemRequest $request)
    {
        $input_items = $request->input('item');
        $currency_code = $request->input('currency_code');

        if (empty($currency_code)) {
            $currency_code = setting('default.currency');
        }

        $json = new \stdClass;

        $debit_sub_total = 0;
        $credit_sub_total = 0;

        if ($input_items) {
            foreach ($input_items as $item) {
                $debit_sub_total += (double) $item['debit'];
                $credit_sub_total += (double) $item['credit'];
            }
        }

        $json->debit_sub_total = money($debit_sub_total, $currency_code, true)->format();
        $json->credit_sub_total = money($credit_sub_total, $currency_code, true)->format();

        $debit_grand_total = $debit_sub_total;
        $credit_grand_total = $credit_sub_total;

        if ($debit_grand_total > $credit_grand_total) {
            $credit_grand_total = $credit_grand_total - $debit_grand_total;
        } elseif ($debit_grand_total < $credit_grand_total) {
            $debit_grand_total = $debit_grand_total - $credit_grand_total;
        }

        $json->debit_grand_total = money($debit_grand_total, $currency_code, true)->format();
        $json->credit_grand_total = money($credit_grand_total, $currency_code, true)->format();

        $json->debit_grand_total_raw = $debit_sub_total;
        $json->credit_grand_total_raw = $credit_sub_total;

        return response()->json($json);
    }
}
