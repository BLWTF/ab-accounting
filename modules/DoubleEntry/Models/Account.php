<?php

namespace Modules\DoubleEntry\Models;

use App\Abstracts\Model;
use App\Models\Banking\Transaction;
use App\Models\Document\Document;
use App\Models\Document\DocumentItem;
use App\Models\Document\DocumentItemTax;
use App\Models\Document\DocumentTotal;
use Bkwld\Cloner\Cloneable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\DoubleEntry\Database\Factories\Account as AccountFactory;

class Account extends Model
{
    use Cloneable, HasFactory;

    protected $table = 'double_entry_accounts';

    protected $appends = ['debit_total', 'credit_total'];

    protected $fillable = ['company_id', 'type_id', 'code', 'name', 'description', 'parent', 'enabled'];

    public function type()
    {
        return $this->belongsTo('Modules\DoubleEntry\Models\Type');
    }

    public function declass()
    {
        return $this->hasManyThrough('Modules\DoubleEntry\Models\DEClass', 'Modules\DoubleEntry\Models\Type', 'class_id');
    }

    public function bank()
    {
        return $this->belongsTo('Modules\DoubleEntry\Models\AccountBank', 'id', 'account_id');
    }

    public function tax()
    {
        return $this->belongsTo('Modules\DoubleEntry\Models\AccountTax', 'id', 'account_id');
    }

    public function ledgers()
    {
        $ledgers = $this->hasMany('Modules\DoubleEntry\Models\Ledger');

        if (request()->has('start_date')) {
            $start_date = request('start_date') . ' 00:00:00';
            $end_date = request('end_date') . ' 23:59:59';

            $ledgers->whereBetween('issued_at', [$start_date, $end_date]);
        }

        $ledgers->whereHasMorph('ledgerable', [
            Document::class,
            DocumentItem::class,
            DocumentItemTax::class,
            DocumentTotal::class,
            Transaction::class,
            Journal::class,
        ], function ($query, $type) {
            if ($type == 'App\Models\Document\Document') {
                $query->accrued();
            }

            if (in_array($type, ['App\Models\Document\DocumentItem', 'App\Models\Document\DocumentItemTax', 'App\Models\Document\DocumentTotal'])) {
                $query->whereHas('document', function ($query) {
                    $query->accrued();
                });
            }
        });

        return $ledgers;
    }

    /**
     * Scope to only include accounts of a given type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $types
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInType($query, $types)
    {
        if (empty($types)) {
            return $query;
        }

        return $query->whereIn('type_id', (array) $types);
    }

    /**
     * Scope code.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $code
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Get the debit total of an account.
     *
     * @return double
     */
    public function getDebitTotalAttribute()
    {
        $total = 0;

        $this->ledgers()->with(['ledgerable'])->each(function ($ledger) use (&$total) {
            $total += $ledger->debit;
        });

        return $total;
    }

    /**
     * Get the credit total of an account.
     *
     * @return double
     */
    public function getCreditTotalAttribute()
    {
        $total = 0;

        $this->ledgers()->with(['ledgerable'])->each(function ($ledger) use (&$total) {
            $total += $ledger->credit;
        });

        return $total;
    }

    /**
     * Get the balance of an account.
     *
     * @return double
     */
    public function getBalanceAttribute()
    {
        $total_debit = 0;
        $total_credit = 0;

        $this->ledgers()->with(['ledgerable'])->each(function ($ledger) use (&$total_debit, &$total_credit) {
            $total_debit += $ledger->debit;
            $total_credit += $ledger->credit;
        });

        return $total_debit - $total_credit;
    }

    public function getOpeningBalanceAttribute()
    {
        $start_date = request('start_date', false);

        if (!$start_date) {
            return 0;
        }

        $ledgers = $this->hasMany('Modules\DoubleEntry\Models\Ledger');

        $ledgers->whereDate('issued_at', '<', $start_date);

        $total_debit = $total_credit = 0;

        $ledgers->each(function ($ledger) use (&$total_debit, &$total_credit) {
            $total_debit += $ledger->debit;
            $total_credit += $ledger->credit;
        });

        $balance = abs($total_debit - $total_credit);

        return $balance;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return AccountFactory::new();
    }
}
