<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Abstracts\Http\Controller;
use App\Http\Requests\Common\Import as ImportRequest;
use App\Traits\Uploads;
use Modules\DoubleEntry\Exports\COA as Export;
use Modules\DoubleEntry\Http\Requests\Account as Request;
use Modules\DoubleEntry\Imports\COA as Import;
use Modules\DoubleEntry\Jobs\Account\CreateAccount;
use Modules\DoubleEntry\Jobs\Account\DeleteAccount;
use Modules\DoubleEntry\Jobs\Account\UpdateAccount;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\DEClass;
use Modules\DoubleEntry\Models\Type;
use Modules\DoubleEntry\Traits\Accounts;

class ChartOfAccounts extends Controller
{
    use Uploads, Accounts;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $classes = DEClass::with('accounts')->get();

        $classes->each(function ($class) {
            $class->accounts->each(function ($account) {
                $account->name = trans($account->name);
            });
        });

        return view('double-entry::chart_of_accounts.index', compact('classes'));
    }

    /**
     * Show the form for viewing the specified resource.
     *
     * @return Response
     */
    public function show()
    {
        return redirect()->route('chart-of-accounts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $types = [];

        $classes = DEClass::pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        $all_types = Type::all()->reject(function ($t) {
            return ($t->id == setting('double-entry.types_tax', 17));
        });

        foreach ($all_types as $type) {
            if (!isset($classes[$type->class_id])) {
                continue;
            }

            $types[$classes[$type->class_id]][$type->id] = trans($type->name);
        }

        ksort($types);

        return view('double-entry::chart_of_accounts.create', compact('types'));
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
        $account = $this->dispatch(new CreateAccount($request));

        $response = [
            'success' => true,
            'error' => false,
            'data' => $account,
            'message' => '',
        ];

        $response['redirect'] = route('chart-of-accounts.index');

        $message = trans('messages.success.added', ['type' => trans_choice('general.accounts', 1)]);

        flash($message)->success();

        return response()->json($response);
    }

    /**
     * Duplicate the specified resource.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function duplicate(Account $chart_of_account)
    {
        $clone = $chart_of_account->duplicate();

        $message = trans('messages.success.duplicated', ['type' => trans_choice('general.accounts', 1)]);

        flash($message)->success();

        return redirect()->route('chart-of-accounts.edit', $clone->id);
    }

    /**
     * Import the specified resource.
     *
     * @param  ImportRequest  $request
     *
     * @return Response
     */
    public function import(ImportRequest $request)
    {
        \Excel::import(new Import(), $request->file('import'));

        $message = trans('messages.success.imported', ['type' => trans_choice('general.accounts', 2)]);

        flash($message)->success();

        return redirect()->route('chart-of-accounts.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function edit(Account $chart_of_account)
    {
        $account = $chart_of_account;

        $account->name = trans($account->name);

        $types = [];

        $classes = DEClass::pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        if ($chart_of_account->type_id == setting('double-entry.types_tax', 17)) {
            $all_types = Type::all();
        } else {
            $all_types = Type::all()->reject(function ($t) {
                return ($t->id == setting('double-entry.types_tax', 17));
            });
        }

        foreach ($all_types as $type) {
            if (!isset($classes[$type->class_id])) {
                continue;
            }

            $types[$classes[$type->class_id]][$type->id] = trans($type->name);
        }

        ksort($types);

        return view('double-entry::chart_of_accounts.edit', compact('account', 'types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Account  $chart_of_account
     * @param  Request  $request
     *
     * @return Response
     */
    public function update(Account $chart_of_account, Request $request)
    {
        if ($chart_of_account->code != $request['code']) {
            $this->updateSettings($chart_of_account->code, $request['code']);
        }

        $chart_of_account = $this->dispatch(new UpdateAccount($chart_of_account, $request));

        $response = [
            'success' => true,
            'error' => false,
            'data' => $chart_of_account,
            'message' => '',
        ];

        $response['redirect'] = route('chart-of-accounts.index');

        $message = trans('messages.success.updated', ['type' => trans_choice('general.accounts', 1)]);

        flash($message)->success();

        return response()->json($response);
    }

    /**
     * Enable the specified resource.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function enable(Account $chart_of_account)
    {
        $chart_of_account->enabled = 1;
        $chart_of_account->save();

        if ($chart_of_account->type_id == setting('double-entry.types_bank', 6)) {
            $core_account = $chart_of_account->bank->bank;

            if ($core_account) {
                $core_account->enabled = 1;
                $core_account->save();
            }
        }

        if ($chart_of_account->type_id == setting('double-entry.types_tax', 17)) {
            $core_tax = $chart_of_account->tax->tax;

            if ($core_tax) {
                $core_tax->enabled = 1;
                $core_tax->save();
            }
        }

        $response = [
            'success' => true,
            'error' => false,
            'data' => $chart_of_account,
            'message' => '',
        ];

        if ($response['success']) {
            $response['message'] = trans('messages.success.enabled', ['type' => trans($chart_of_account->name)]);
        }

        return response()->json($response);
    }

    /**
     * Disable the specified resource.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function disable(Account $chart_of_account)
    {
        $chart_of_account->enabled = 0;
        $chart_of_account->save();

        if ($chart_of_account->type_id == setting('double-entry.types_bank', 6)) {
            $core_account = $chart_of_account->bank->bank;

            if ($core_account) {
                $core_account->enabled = 0;
                $core_account->save();
            }
        }

        if ($chart_of_account->type_id == setting('double-entry.types_tax', 17)) {
            $core_tax = $chart_of_account->tax->tax;

            if ($core_tax) {
                $core_tax->enabled = 0;
                $core_tax->save();
            }
        }

        $response = [
            'success' => true,
            'error' => false,
            'data' => $chart_of_account,
            'message' => '',
        ];

        if ($response['success']) {
            $response['message'] = trans('messages.success.disabled', ['type' => trans($chart_of_account->name)]);
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Account  $chart_of_account
     *
     * @return Response
     */
    public function destroy(Account $chart_of_account)
    {
        $response = [
            'success' => true,
            'error' => false,
            'data' => '',
            'message' => '',
            'redirect' => route('chart-of-accounts.index'),
        ];

        $relationships = $this->countRelationships($chart_of_account, [
            'bank' => 'bank_accounts',
            'tax' => 'tax_rates',
            'ledgers' => 'ledgers',
        ]);

        $settings = $this->countSettings($chart_of_account);

        if (empty($relationships) && empty($settings)) {
            $this->dispatch(new DeleteAccount($chart_of_account));

            $message = trans('messages.success.deleted', ['type' => trans_choice('general.accounts', 1)]);

            flash($message)->success();
        } else {
            $text = array_merge($relationships, $settings);
            $message = trans('messages.warning.deleted', ['name' => trans($chart_of_account->name), 'text' => implode(', ', $text)]);

            flash($message)->warning();
        }

        return response()->json($response);
    }

    /**
     * Export the specified resource.
     *
     * @return Response
     */
    public function export()
    {
        return \Excel::download(new Export(), 'accounts' . '.xlsx');
    }
}
