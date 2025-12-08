<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $connection = config('plog.database.connection', 'plog');
        $table = config('plog.database.table', 'plog_entries');

        if (Schema::connection($connection)->hasTable($table)) {
            if (!Schema::connection($connection)->hasColumn($table, 'response_time')) {
                Schema::connection($connection)->table($table, function (Blueprint $table) {
                    $table->float('response_time')->nullable()->after('retention_group');
                });
            }
        }
    }

    public function down()
    {
        $connection = config('plog.database.connection', 'plog');
        $table = config('plog.database.table', 'plog_entries');

        if (Schema::connection($connection)->hasTable($table)) {
            Schema::connection($connection)->table($table, function (Blueprint $table) {
                $table->dropColumn('response_time');
            });
        }
    }
};
