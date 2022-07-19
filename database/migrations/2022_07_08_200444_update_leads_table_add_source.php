<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLeadsTableAddSource extends Migration
{
    /**
     * Add a `source` column as Leads could probably come from multiple APIs (eventually)
     * The Foreign ID should be unique to each Source
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('source')->index()->after('foreign_id');
            // NOTE: In The Future This Could Be A ForeignID To Data Source Table
            $table->unique(['foreign_id', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('source');
            $table->dropUnique(['foreign_id', 'source']);
        });
    }
}
