<el-popover
    popper-class="p-0 h-0"
    placement="bottom"
    width="300"
    trigger="click">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center mb-2">
                <div class="col-md-12">
                    {{ trans('double-entry::general.document.detail', ['class' => Str::lower(trans($document_type_class)), 'type' => Str::lower(trans_choice($document_type_name, 2))]) }}
                </div>
            </div>
            <div class="row align-items-center">
                @include('double-entry::partials.input_account_group', ['name' => $input_account_name, 'text' => $input_account_text, 'values' => $de_accounts, 'attributes' => $input_account_attributes, 'col' => $input_account_col])
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-link btn-sm p-0" slot="reference">
        {{ trans('double-entry::general.edit_account', ['type' => trans($document_type_class)]) }}
    </button>
</el-popover>
