@stack('de_account_id_td_start')
<td class="border-right-0 border-bottom-0">
    @if($action == 'create')
    <akaunting-select
        class="mb-0"
        :form-classes="[{'has-error': form.errors.has('items.' + index + '.de_account_id') }]"
        icon=""
        title=""
        placeholder="{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
        name="de_account_id"
        :options="{{ json_encode($de_accounts) }}"
        :value="row.de_account_id"
        :model="this.item_accounts[row.item_id]"
        :group="true"
        @interface="row.de_account_id = $event"
        :form-error="form.errors.get('items.' + index + '.de_account_id')"
        :no-data-text="'{{ trans('general.no_data') }}'"
        :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
    ></akaunting-select>
    @else
    <akaunting-select
        class="mb-0"
        :form-classes="[{'has-error': form.errors.has('items.' + index + '.de_account_id') }]"
        icon=""
        title=""
        placeholder="{{ trans('general.form.select.field', ['field' => trans_choice('general.accounts', 1)]) }}"
        name="de_account_id"
        :options="{{ json_encode($de_accounts) }}"
        :value="row.de_account_id"
        :model="this.item_accounts[index]"
        :group="true"
        @interface="row.de_account_id = $event"
        :form-error="form.errors.get('items.' + index + '.de_account_id')"
        :no-data-text="'{{ trans('general.no_data') }}'"
        :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
    ></akaunting-select>
    @endif
</td>
@stack('de_account_id_td_end')
