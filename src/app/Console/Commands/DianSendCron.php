<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DianSendCron extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'command:dianSend';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Organiza la informacion que se envia a DIAN';


	use Dian;
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return int
	 */
	public function handle()
	{
		print_r($this->invoice());
		print_r($this->note());
	}
}
