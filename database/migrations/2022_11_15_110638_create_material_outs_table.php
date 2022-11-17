<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->create('material_outs', function (Blueprint $table) {
            $database = \DB::connection('mysql_system')->getDatabaseName();
            $table->id();
            $table->string('code', 15)->unique()->nullable();
            $table->dateTime('at');
            $table->string('type');
            $table->foreignId('created_by_user_id')
            ->constrained("$database.users")
            ->cascadeOnUpdate()
            ->restrictOnDelete();
            $table->foreignId('last_updated_by_user_id')
            ->constrained("$database.users")
            ->cascadeOnUpdate()
            ->restrictOnDelete();
            $table->text('note')->nullable();
            $table->string('desc')->nullable();
            $table->text('history_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->dropIfExists('material_outs');
    }
}
