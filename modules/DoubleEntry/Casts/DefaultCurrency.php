<?php

namespace Modules\DoubleEntry\Casts;

use App\Traits\Currencies;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DefaultCurrency implements CastsAttributes
{
    use Currencies;

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        switch (get_class($model->ledgerable)) {
            case 'App\Models\Document\Document':
            case 'App\Models\Banking\Transaction':

                $value = $this->convertToDefault($value ?? 0, $model->ledgerable->currency_code, $model->ledgerable->currency_rate);
                break;

            case 'App\Models\Document\DocumentItem':
            case 'App\Models\Document\DocumentTotal':

                $value = $this->convertToDefault($value ?? 0, $model->ledgerable->document->currency_code, $model->ledgerable->document->currency_rate);
                break;

            default:
                $value = (double) $value;

                break;
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        return $value;
    }
}
