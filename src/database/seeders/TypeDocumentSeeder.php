<?php

namespace Database\Seeders;

use App\Models\TypeDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TypeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        TypeDocument::truncate();
        Schema::enableForeignKeyConstraints();

        $sql = file_get_contents(database_path() . '/inserts/type_document.sql');
        DB::statement($sql);
    }
}
