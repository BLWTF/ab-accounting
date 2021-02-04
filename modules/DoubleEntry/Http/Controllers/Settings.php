<?php

namespace Modules\DoubleEntry\Http\Controllers;

use App\Abstracts\Http\Controller;
use Illuminate\Http\Response;
use Modules\DoubleEntry\Http\Requests\Setting as Request;
use Modules\DoubleEntry\Models\Account;
use Modules\DoubleEntry\Models\DEClass;
use Modules\DoubleEntry\Models\Type;

class Settings extends Controller
{
    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        $account_options = $type_options = [];

        $accounts = Account::select(['type_id', 'code', 'name'])->enabled()->get();
        $types = Type::all(['id', 'class_id', 'name']);
        $classes = DEClass::all(['id', 'name']);

        $classes_plucked = $classes->pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        $types_plucked = $types->pluck('name', 'id')->map(function ($name) {
            return trans($name);
        })->toArray();

        foreach ($accounts as $account) {
            if (!isset($types_plucked[$account->type_id])) {
                continue;
            }

            $account_options[$types_plucked[$account->type_id]][$account->code] = $account->code . ' - ' . trans($account->name);
        }

        foreach ($types as $type) {
            if (!isset($classes_plucked[$type->class_id])) {
                continue;
            }

            $type_options[$classes_plucked[$type->class_id]][$type->id] = trans($type->name);
        }

        return view('double-entry::settings.edit', compact('account_options', 'type_options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function update(Request $request)
    {
        setting()->set('double-entry.accounts_receivable', $request['accounts_receivable']);
        setting()->set('double-entry.accounts_payable', $request['accounts_payable']);
        setting()->set('double-entry.accounts_sales', $request['accounts_sales']);
        setting()->set('double-entry.accounts_expenses', $request['accounts_expenses']);
        setting()->set('double-entry.accounts_sales_discount', $request['accounts_sales_discount']);
        setting()->set('double-entry.accounts_purchase_discount', $request['accounts_purchase_discount']);
        setting()->set('double-entry.accounts_owners_contribution', $request['accounts_owners_contribution']);
        setting()->set('double-entry.types_bank', $request['types_bank']);
        setting()->set('double-entry.types_tax', $request['types_tax']);
        setting()->save();

        $response = [
            'success' => true,
            'error' => false,
            'data' => null,
            'message' => '',
        ];

        $response['redirect'] = route('settings.index');

        $message = trans('messages.success.updated', ['type' => trans_choice('general.settings', 2)]);

        flash($message)->success();

        return response()->json($response);
    }
}
