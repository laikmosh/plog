<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('plog')->create('plog_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique()->index();
            $table->string('method', 10);
            $table->text('url');
            $table->json('headers')->nullable();
            $table->json('body')->nullable();
            $table->json('query_params')->nullable();
            $table->json('cookies')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('plog')->dropIfExists('plog_requests');
    }
};