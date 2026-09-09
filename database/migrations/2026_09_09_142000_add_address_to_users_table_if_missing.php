<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressToUsersTableIfMissing extends Migration
{
    /**
     * Patient search SELECT includes users.address. Fresh crmsmart copies
     * from the recovered schema did not have this column.
     */
    public function up()
    {
        if (! Schema::hasColumn('users', 'address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('address')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'address')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }
}
