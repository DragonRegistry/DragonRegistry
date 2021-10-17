<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToPackageTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->softDeletes()->after('storage_path');
        });
        Schema::table('package_versions', function (Blueprint $table) {
            $table->softDeletes()->after('version');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('package_tables', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('package_versions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
