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

        if (Schema::connection($connection)->hasTable($table) && !Schema::connection($connection)->hasColumn($table, 'stack_trace')) {
            Schema::connection($connection)->table($table, function (Blueprint $table) {
                $table->json('stack_trace')->nullable()->after('tags');
            });
        }
    }

    public function down()
    {
        $connection = config('plog.database.connection', 'plog');
        $table = config('plog.database.table', 'plog_entries');

        if (Schema::connection($connection)->hasTable($table)) {
            Schema::connection($connection)->table($table, function (Blueprint $table) {
                $table->dropColumn('stack_trace');
            });
        }
    }
};