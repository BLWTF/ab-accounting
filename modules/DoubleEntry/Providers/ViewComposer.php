<?php

namespace Modules\DoubleEntry\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class ViewComposer extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        View::composer(['components.documents.form.line-item'], 'Modules\DoubleEntry\Http\ViewComposers\DocumentItem');

        // Apps
        View::composer(['receipt::show'], 'Modules\DoubleEntry\Http\ViewComposers\ReceiptInput');

        View::composer(['common.items.create', 'common.items.edit'], 'Modules\DoubleEntry\Http\ViewComposers\Items');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
