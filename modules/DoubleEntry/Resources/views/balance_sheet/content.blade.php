<div class="table-responsive overflow-auto mt-4">
    @foreach($class->de_classes as $de_class)
        <table class="table">
            <thead>
                <tr class="row mx-0 font-size-unset">
                    <th class="col-sm-12 border-top-0 border-bottom-1">
                        <h4 class="mb-0 text-uppercase">{{ trans($de_class->name) }}</h4>
                    </th>
                </tr>
            </thead>
        </table>
        @foreach($de_class->types as $type)
            @if (!empty($type->total))
                <table class="table table-hover">
                    <thead>
                        <tr class="row mx-0 font-size-unset">
                            <th class="col-sm-12 border-0">
                                <h5 class="pl-4 mb-0 font-weight-bolder text-capitalize">{{ trans($type->name) }}</h5>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($class->de_accounts[$type->id] as $account)
                            @if (!empty($account->de_balance))
                                <tr class="row mx-0 font-size-unset">
                                    <td class="col-sm-9 pl-6 border-0">{{ trans($account->name) }}</td>
                                    <td class="col-sm-3 text-right border-0">@money($account->de_balance, setting('default.currency'), true)</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="row mx-0 font-size-unset">
                            <th class="col-sm-9 pl-5 border-0"><h5 class="font-weight-bolder">{{ trans('double-entry::general.total_type', ['type' => trans($type->name)]) }}</h5></th>
                            <th class="col-sm-3 text-right mb-3 border-0">@money($type->total, setting('default.currency'), true)</th>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endforeach
        <table class="table">
            <thead>
                <tr class="row mx-0 font-size-unset mb-4">
                    <td class="col-sm-9"><h4 class="mb-0 text-uppercase">{{ trans('double-entry::general.total_type', ['type' => trans($de_class->name)]) }}</h4></td>
                    <td class="col-sm-3 text-right">@money($de_class->total, setting('default.currency'), true)</td>
                </tr>
            </thead>
        </table>
    @endforeach
    <table class="table">
        <thead>
            <tr class="row mx-0 font-size-unset mb-4">
                <td class="col-sm-9"><h4 class="mb-0 text-uppercase">{{ trans('double-entry::general.total_type', ['type' => trans('double-entry::general.liabilities_equities')]) }}</h4></td>
                <td class="col-sm-3 text-right">@money($class->total_liabilities_equity, setting('default.currency'), true)</td>
            </tr>
        </thead>
    </table>
</div>
