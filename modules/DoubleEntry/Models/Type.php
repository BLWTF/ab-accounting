<?php

namespace Modules\DoubleEntry\Models;

use App\Abstracts\Model;

class Type extends Model
{
    protected $table = 'double_entry_types';

    protected $tenantable = false;

    protected $fillable = ['class_id', 'name'];

    public function accounts()
    {
        return $this->hasMany('Modules\DoubleEntry\Models\Account');
    }

    public function declass()
    {
        return $this->belongsTo('Modules\DoubleEntry\Models\DEClass', 'class_id', 'id');
    }
}
