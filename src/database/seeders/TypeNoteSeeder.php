<?php

namespace Database\Seeders;

use App\Models\TypeNote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TypeNoteSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Schema::disableForeignKeyConstraints();
		TypeNote::truncate();
		Schema::enableForeignKeyConstraints();

		$sql = file_get_contents(database_path() . '/inserts/type_note.sql');
		DB::statement($sql);
	}
}
