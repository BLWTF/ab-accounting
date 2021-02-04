<?php

namespace Modules\DoubleEntry\Models;

use App\Abstracts\Model;
use Modules\DoubleEntry\Casts\DefaultCurrency;

class Ledger extends Model
{
    protected $table = 'double_entry_ledger';

    protected $fillable = ['company_id', 'account_id', 'ledgerable_id', 'ledgerable_type', 'issued_at', 'entry_type', 'debit', 'credit'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'debit' => DefaultCurrency::class,
        'credit' => DefaultCurrency::class,
    ];

    public function account()
    {
        return $this->belongsTo('Modules\DoubleEntry\Models\Account')->withDefault(['name' => trans('general.na')]);
    }

    public function ledgerable()
    {
        return $this->morphTo();
    }

    /**
     * Scope record.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $id
     * @param $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecord($query, $id, $type)
    {
        return $query->where('ledgerable_id', $id)->where('ledgerable_type', $type);
    }

    public function getDescriptionAttribute()
    {
        $ledgerable = $this->ledgerable;

        if (!$ledgerable) {
            return '';
        }

        switch ($this->ledgerable_type) {
            case 'App\Models\Banking\Transaction':
            case 'Modules\DoubleEntry\Models\Journal':

                return $ledgerable->description;

            case 'App\Models\Document\Document':

                if ($ledgerable->type == 'invoice') {
                    $label = trans('invoices.invoice_number');
                } else {
                    $label = trans('bills.bill_number');
                }

                return $label . ': ' . $ledgerable->document_number;

            case 'App\Models\Document\DocumentItem':
            case 'App\Models\Document\DocumentItemTax':

                if ($ledgerable->type == 'invoice') {
                    $label = trans('invoices.invoice_number');
                } else {
                    $label = trans('bills.bill_number');
                }

                return $label . ': ' . $ledgerable->document->document_number;
        }
    }
}
