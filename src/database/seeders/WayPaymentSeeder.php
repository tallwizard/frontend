<?php

namespace Database\Seeders;

use App\Models\WayPayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WayPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        WayPayment::truncate();
        Schema::enableForeignKeyConstraints();

        $sql = file_get_contents(database_path() . '/inserts/way_payment.sql');
        DB::statement($sql);
    }
}
