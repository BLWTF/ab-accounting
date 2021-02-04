<?php

namespace Modules\DoubleEntry\Listeners\Update\V20;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Models\Module\Module;
use App\Traits\Permissions;
use Illuminate\Support\Facades\DB;

class Version207 extends Listener
{
    use Permissions;

    const ALIAS = 'double-entry';

    const VERSION = '2.0.7';

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        if ($this->skipThisUpdate($event)) {
            return;
        }

        $this->updateDatabase();

        $this->updatePermissions();
    }

    protected function updateDatabase()
    {
        $modules = Module::withoutGlobalScopes()->alias('double-entry')->cursor();

        foreach ($modules as $module) {
            $dashboard = DB::table('dashboards')
                ->where('company_id', $module->company_id)
                ->where('name', trans('double-entry::general.name'))
                ->first();

            if (!$dashboard) {
                continue;
            }

            DB::table('widgets')
                ->where('company_id', $module->company_id)
                ->where('dashboard_id', $dashboard->id)
                ->where('class', 'App\Widgets\TotalIncome')
                ->update([
                    'class' => 'Modules\DoubleEntry\Widgets\TotalIncomeByCoa',
                    'name' => trans('double-entry::widgets.total_income_by_coa'),
                ]);

            DB::table('widgets')
                ->where('company_id', $module->company_id)
                ->where('dashboard_id', $dashboard->id)
                ->where('class', 'App\Widgets\TotalExpenses')
                ->update([
                    'class' => 'Modules\DoubleEntry\Widgets\TotalExpensesByCoa',
                    'name' => trans('double-entry::widgets.total_expenses_by_coa'),
                ]);

            DB::table('widgets')
                ->where('company_id', $module->company_id)
                ->where('dashboard_id', $dashboard->id)
                ->where('class', 'App\Widgets\TotalProfit')
                ->update([
                    'class' => 'Modules\DoubleEntry\Widgets\TotalProfitByCoa',
                    'name' => trans('double-entry::widgets.total_profit_by_coa'),
                ]);
        }
    }

    protected function updatePermissions()
    {
        if ($p = Permission::where('name', 'read-double-entry-widgets-total-income-by-coa')->pluck('id')->first()) {
            return;
        }

        $attach_permissions[] = Permission::firstOrCreate([
            'name' => 'read-double-entry-widgets-total-income-by-coa',
            'display_name' => 'Read Double-Entry Widgets Total Income By COA',
            'description' => 'Read Double-Entry Widgets Total Income By COA',
        ]);

        $attach_permissions[] = Permission::firstOrCreate([
            'name' => 'read-double-entry-widgets-total-expenses-by-coa',
            'display_name' => 'Read Double-Entry Widgets Total Expenses By COA',
            'description' => 'Read Double-Entry Widgets Total Expenses By COA',
        ]);

        $attach_permissions[] = Permission::firstOrCreate([
            'name' => 'read-double-entry-widgets-total-profit-by-coa',
            'display_name' => 'Read Double-Entry Widgets Total Profit By COA',
            'description' => 'Read Double-Entry Widgets Total Profit By COA',
        ]);

        $roles = Role::all()->filter(function ($r) {
            return $r->hasPermission('read-double-entry-chart-of-accounts');
        });

        foreach ($roles as $role) {
            foreach ($attach_permissions as $permission) {
                $this->attachPermission($role, $permission);
            }
        }
    }
}
