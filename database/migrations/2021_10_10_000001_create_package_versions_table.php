<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Package::class)->constrained();
            $table->foreignIdFor(\App\Models\User::class, 'publisher_user_id')->constrained('users');
            $table->string('version');
            $table->timestamps();

            $table->unique(['version', 'package_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_versions');
    }
}
