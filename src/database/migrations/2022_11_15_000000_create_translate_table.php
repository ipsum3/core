<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (count(config('ipsum.translate.locales')) < 2) {
            return;
        }

        Schema::create('translates', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('translatable');
            $table->string('locale');
            $table->string('attribut');
            $table->text('value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translates');
    }
};
