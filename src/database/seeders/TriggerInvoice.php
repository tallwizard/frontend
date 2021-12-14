<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TriggerInvoice extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Schema::disableForeignKeyConstraints();
        DB::unprepared('DROP FUNCTION IF EXISTS `next_invoice`;');
        DB::unprepared('DROP TRIGGER IF EXISTS `trigger_invoice`;');
        Schema::enableForeignKeyConstraints();
        $sql = <<<SQL
		CREATE FUNCTION `next_invoice` (`p_sequence_name` VARCHAR(10)) RETURNS INT UNSIGNED READS SQL DATA
		BEGIN
			INSERT INTO `sequences` (`name`, `value`) VALUES (`p_sequence_name`, LAST_INSERT_ID(1)) ON DUPLICATE KEY UPDATE `value` = LAST_INSERT_ID(`value` + 1);
			RETURN LAST_INSERT_ID();
		END
		SQL;
        DB::unprepared($sql);
        $sql = <<<SQL
		CREATE TRIGGER `trigger_invoice` BEFORE INSERT ON `invoice_headers` FOR EACH ROW
			BEGIN
				SET NEW.`consecutive` := IF(NEW.`consecutive` = 0,`next_invoice`(NEW.`code`),NEW.`consecutive`);
			END
		SQL;
        DB::unprepared($sql);
    }
}
