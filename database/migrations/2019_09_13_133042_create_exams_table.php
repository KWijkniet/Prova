<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('year');
            $table->unsignedBigInteger('exam_states_id')->default(1);
            $table->unsignedBigInteger('education_id')->default(1);
            $table->unsignedBigInteger('domain_id')->default(2);
            $table->longtext('template');

            $table->foreign('education_id')
                ->references('id')->on('educations')
                ->onDelete('cascade');
            $table->foreign('domain_id')
                ->references('id')->on('domains')
                ->onDelete('cascade');
            $table->foreign('exam_states_id')
                ->references('id')->on('exam_states')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exams');
    }
}
