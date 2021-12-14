<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolesSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Schema::disableForeignKeyConstraints();
		Role::truncate();
		Schema::enableForeignKeyConstraints();
		$sql = file_get_contents(database_path() . '/inserts/role.sql');
		DB::statement($sql);
	}
}
