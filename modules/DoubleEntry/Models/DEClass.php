<?php

namespace Modules\DoubleEntry\Models;

use App\Abstracts\Model;

class DEClass extends Model
{
    protected $table = 'double_entry_classes';

    protected $tenantable = false;

    protected $fillable = ['name'];

    public function types()
    {
        return $this->hasMany('Modules\DoubleEntry\Models\Type', 'class_id');
    }

    public function accounts()
    {
        return $this->hasManyThrough('Modules\DoubleEntry\Models\Account', 'Modules\DoubleEntry\Models\Type', 'class_id', 'type_id');
    }
}
