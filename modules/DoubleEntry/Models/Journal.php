<?php

namespace Modules\DoubleEntry\Models;

use App\Abstracts\Model;
use App\Traits\Currencies;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\DoubleEntry\Database\Factories\Journal as JournalFactory;

class Journal extends Model
{
    use Currencies, HasFactory;

    protected $table = 'double_entry_journals';

    protected $fillable = ['company_id', 'paid_at', 'amount', 'description', 'reference'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'double',
    ];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['paid_at'];

    public function ledger()
    {
        return $this->morphOne('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
    }

    public function ledgers()
    {
        return $this->morphMany('Modules\DoubleEntry\Models\Ledger', 'ledgerable');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return JournalFactory::new();
    }
}
