<div class="card">
    <div class="table-responsive">
        <table class="table table-flush table-hover">
            <thead class="thead-light">
                <tr class="row font-size-unset table-head-line">
                    <th class="col-sm-3 text-uppercase">{{ trans('general.date') }}</th>
                    <th class="col-sm-3 text-uppercase">{{ trans('general.description') }}</th>
                    <th class="col-sm-2 text-uppercase text-right">{{ trans('double-entry::general.debit') }}</th>
                    <th class="col-sm-2 text-uppercase text-right">{{ trans('double-entry::general.credit') }}</th>
                    <th class="col-sm-2 text-uppercase text-right">{{ trans('general.balance') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@foreach($class->de_classes as $de_class)
    @foreach($de_class->types->sortBy('id') as $de_type)
        @foreach($class->de_accounts->where('class_id', $de_class->id)->where('type_id', $de_type->id)->sortBy('name') as $account)
            @php
                $closing_balance = $account->opening_balance;
            @endphp
            <div class="card">
                
                @if(empty(request()->segment(4)))
                    <div class="card-header border-bottom-0">
                        {{ trans($account->name) }} ({{ trans($account->type->name) }})
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-flush">
                        <thead class="thead-light">
                            @if(request()->segment(4) == 'export' || request()->segment(4) == 'print')
                                <tr class="row font-size-unset table-head-line">
                                    <th class="col-sm-12">{{ trans($account->name) }} ({{ trans($account->type->name) }})</th>
                                </tr>
                            @endif
                            <tr class="row font-size-unset table-head-line">
                                <th class="col-sm-3">{{ trans('accounts.opening_balance') }}</th>
                                <th class="col-sm-3">&nbsp;</th>
                                <th class="col-sm-2">&nbsp;</th>
                                <th class="col-sm-2">&nbsp;</th>
                                <th class="col-sm-2 text-right">@money($account->opening_balance, setting('default.currency'), true)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $debit_total = 0;
                                $credit_total = 0;
                            @endphp
                            @foreach($account->ledgers as $ledger)
                                @php
                                    $debit = $ledger->debit;
                                    $debit_total += $debit;
                                    $credit = $ledger->credit;
                                    $credit_total += $credit;
                                    $closing_balance += $debit - $credit;
                                @endphp
                                <tr class="row font-size-unset">
                                    <td class="col-sm-3">@date($ledger->issued_at)</td>
                                    <td class="col-sm-3 long-texts">{{ $ledger->description }}</td>
                                    <td class="col-sm-2 text-right">@if (!empty($debit)) @money((double) $debit, setting('default.currency'), true) @endif</td>
                                    <td class="col-sm-2 text-right">@if (!empty($credit)) @money((double) $credit, setting('default.currency'), true) @endif</td>
                                    <td class="col-sm-2 text-right">@money((double) abs($closing_balance), setting('default.currency'), true)</td>
                                </tr>
                            @endforeach
                            <tr class="row font-size-unset table-head-line">
                                <th class="col-sm-3">{{ trans('double-entry::general.totals_balance') }}</th>
                                <th class="col-sm-3">&nbsp;</th>
                                <th class="col-sm-2 text-right">@money($debit_total, setting('default.currency'), true)</th>
                                <th class="col-sm-2 text-right">@money($credit_total, setting('default.currency'), true)</th>
                                <th class="col-sm-2 text-right">@money(abs($closing_balance), setting('default.currency'), true)</th>
                            </tr>
                            <tr class="row font-size-unset table-head-line">
                                <th class="col-sm-10" colspan="3">{{ trans('double-entry::general.balance_change') }}</th>
                                <th class="col-sm-2 text-right">@money(abs($closing_balance - $account->opening_balance), setting('default.currency'), true)</th>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        @endforeach
    @endforeach
@endforeach
