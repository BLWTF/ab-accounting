@extends('layouts.admin')

@section('title', trans_choice('general.settings', 2))

@section('content')
        {!! Form::open([
            'id' => 'double-entry-setting',
            'method' => 'POST',
            'route' => ['double-entry.settings.update'],
            '@submit.prevent' => 'onSubmit',
            '@keydown' => 'form.errors.clear($event.target.name)',
            'files' => true,
            'role' => 'form',
            'class' => 'form-loading-button',
            'novalidate' => true
        ]) !!}

        <div class="card">
            <div class="card-body">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('double-entry::general.default_type', ['type' => trans_choice('double-entry::general.chart_of_accounts', 2)]) }}</h3>
                </div>
                <div class="row">
                    {{ Form::selectGroupGroup('accounts_receivable', trans('double-entry::general.accounts.receivable'), 'book', $account_options, old('accounts_receivable', setting('double-entry.accounts_receivable'))) }}

                    {{ Form::selectGroupGroup('accounts_payable', trans('double-entry::general.accounts.payable'), 'book', $account_options, old('accounts_payable', setting('double-entry.accounts_payable'))) }}

                    {{ Form::selectGroupGroup('accounts_sales', trans('double-entry::general.accounts.sales'), 'book', $account_options, old('accounts_sales', setting('double-entry.accounts_sales'))) }}

                    {{ Form::selectGroupGroup('accounts_expenses', trans('double-entry::general.accounts.expenses'), 'book', $account_options, old('accounts_expenses', setting('double-entry.accounts_expenses'))) }}

                    {{ Form::selectGroupGroup('accounts_sales_discount', trans('double-entry::general.accounts.sales_discount'), 'book', $account_options, old('accounts_sales_discount', setting('double-entry.accounts_sales_discount'))) }}

                    {{ Form::selectGroupGroup('accounts_purchase_discount', trans('double-entry::general.accounts.purchase_discount'), 'book', $account_options, old('accounts_purchase_discount', setting('double-entry.accounts_purchase_discount'))) }}

                    {{ Form::selectGroupGroup('accounts_owners_contribution', trans('double-entry::general.accounts.owners_contribution'), 'book', $account_options, old('accounts_owners_contribution', setting('double-entry.accounts_owners_contribution'))) }}
                 </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('double-entry::general.default_type', ['type' => trans_choice('general.types', 2)]) }}</h3>
                </div>
                <div class="row">
                    {{ Form::selectGroupGroup('types_bank', trans('double-entry::general.bank_cash'), 'book', $type_options, old('types_bank', setting('double-entry.types_bank', 6))) }}

                    {{ Form::selectGroupGroup('types_tax', trans_choice('general.taxes', 1), 'book', $type_options, old('types_tax', setting('double-entry.types_tax', 17))) }}
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-footer">
                <div class="row float-right">
                    {{ Form::saveButtons('/') }}
                </div>
            </div>
        </div>

        {!! Form::close() !!}
@endsection

@push('scripts_start')
    <script src="{{ asset('modules/DoubleEntry/Resources/assets/js/double-entry-settings.min.js?v=' . module_version('double-entry')) }}"></script>
@endpush
