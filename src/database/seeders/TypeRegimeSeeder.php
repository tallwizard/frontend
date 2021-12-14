<?php

namespace Database\Seeders;

use App\Models\TypeRegime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TypeRegimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        TypeRegime::truncate();
        Schema::enableForeignKeyConstraints();

        $sql = file_get_contents(database_path() . '/inserts/type_regime.sql');
        DB::statement($sql);
    }
}
