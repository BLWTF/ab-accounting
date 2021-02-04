<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DoubleEntryV2019 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('double_entry_account_item', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('account_id');
            $table->integer('item_id');
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('double_entry_account_item');
    }
}
