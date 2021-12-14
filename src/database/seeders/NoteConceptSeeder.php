<?php

namespace Database\Seeders;

use App\Models\NoteConcept;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NoteConceptSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Schema::disableForeignKeyConstraints();
		NoteConcept::truncate();
		Schema::enableForeignKeyConstraints();

		$sql = file_get_contents(database_path() . '/inserts/note_concept.sql');
		DB::statement($sql);
	}
}
