<?php

namespace Modules\DoubleEntry\Database\Seeds;

use App\Abstracts\Model;
use App\Traits\Permissions as Helper;
use Illuminate\Database\Seeder;

class Permissions extends Seeder
{
    use Helper;

    public $alias = 'double-entry';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->create();

        Model::reguard();
    }

    private function create()
    {
        $this->attachPermissionsToAdminRoles([
            $this->alias . '-chart-of-accounts' => 'c,r,u,d',
            $this->alias . '-journal-entry' => 'c,r,u,d',
            $this->alias . '-settings' => 'r,u',
        ]);
    }
}
