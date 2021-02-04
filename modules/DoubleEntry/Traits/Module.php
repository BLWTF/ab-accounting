<?php

namespace Modules\DoubleEntry\Traits;

use App\Models\Module\Module as Model;

trait Module
{
    protected function isModuleDisabled()
    {
        $module = Model::alias('double-entry')->enabled()->first();

        if ($module instanceof Model) {
            return false;
        }

        return true;
    }
}
