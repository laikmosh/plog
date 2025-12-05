<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::connection('plog')->hasTable('plog_entries') && !Schema::connection('plog')->hasColumn('plog_entries', 'stack_trace')) {
            Schema::connection('plog')->table('plog_entries', function (Blueprint $table) {
                $table->json('stack_trace')->nullable()->after('tags');
            });
        }
    }

    public function down()
    {
        Schema::connection('plog')->table('plog_entries', function (Blueprint $table) {
            $table->dropColumn('stack_trace');
        });
    }
};