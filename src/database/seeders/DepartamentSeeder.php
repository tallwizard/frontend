<?php

namespace Database\Seeders;

use App\Models\Departament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DepartamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Departament::truncate();
        Schema::enableForeignKeyConstraints();

        $sql = file_get_contents(database_path() . '/inserts/departament.sql');
        DB::statement($sql);
    }
}
