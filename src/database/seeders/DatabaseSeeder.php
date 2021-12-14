<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->call(RolesSeeder::class);
		$this->call(PaymentMethodSeeder::class);
		$this->call(WayPaymentSeeder::class);
		$this->call(TypeRegimeSeeder::class);
		$this->call(TypeDocumentSeeder::class);
		$this->call(TypeClientSeeder::class);
		$this->call(CountrySeeder::class);
		$this->call(DepartamentSeeder::class);
		$this->call(CitySeeder::class);
		$this->call(TypeNoteSeeder::class);
		$this->call(NoteConceptSeeder::class);
	
		// $this->call(TriggerInvoice::class);
	}
}
