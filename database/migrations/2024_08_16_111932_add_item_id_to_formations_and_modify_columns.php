<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddItemIdToFormationsAndModifyColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade')->after('compo_id');
            $table->boolean('star')->default(false)->after('slot_table');
        });

        $connection = config('database.default');

        if ($connection === 'pgsql') {
            DB::statement('
                UPDATE formations 
                SET slot_table = (slot_table::json->>\'key\')::integer
                WHERE slot_table IS NOT NULL AND slot_table::json->>\'key\' ~ \'^\d+$\';
            ');

            DB::statement('ALTER TABLE formations ALTER COLUMN slot_table TYPE integer USING slot_table::integer;');
        } elseif ($connection === 'mysql') {
            Schema::table('formations', function (Blueprint $table) {
                $table->integer('slot_table')->change();
            });
        } else {
            throw new Exception("Database connection [$connection] not supported.");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('database.default');

        if ($connection === 'pgsql') {
            DB::statement('ALTER TABLE formations ALTER COLUMN slot_table TYPE JSON USING slot_table::text::json;');
        } elseif ($connection === 'mysql') {
            Schema::table('formations', function (Blueprint $table) {
                $table->json('slot_table')->change();
            });
        } else {
            throw new Exception("Database connection [$connection] not supported.");
        }

        Schema::table('formations', function (Blueprint $table) {
            $table->dropColumn('star');
            $table->dropForeign(['item_id']);
            $table->dropColumn('item_id');
        });
    }
}
