<?php

namespace Modules\DoubleEntry\Models;

use App\Abstracts\Model;

class AccountItem extends Model
{
    protected $table = 'double_entry_account_item';

    protected $fillable = ['company_id', 'account_id', 'item_id', 'type'];

    public function account()
    {
        return $this->belongsTo('Modules\DoubleEntry\Models\Account', 'account_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item', 'item_id', 'id');
    }
}
