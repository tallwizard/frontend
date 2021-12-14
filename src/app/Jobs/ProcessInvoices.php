<?php

namespace App\Jobs;

use App\Models\InvoiceHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessInvoices implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $invoice;
	protected $header = array("accept: */*", "Content-Type: application/json");
	// public $url = 'http://cent-sihos.nuevoerp.co/sys/src/';
	protected $url = 'http://localhost:4002/sys/src/';
	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(InvoiceHeader $invoice)
	{
		$this->invoice = $invoice;
	}

	// public $uniqueFor = 10;

	// public function uniqueId()
	// {
	// 	Log::info($this->invoice->id);
	// 	return $this->invoice->id;
	// }
	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		Log::info('Inicia');
		$mh = curl_multi_init();
		$ch = curl_init($this->url);
		$handles[] = $ch;


		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->invoice));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_multi_add_handle($mh, $ch);

		$running = null;
		do {
			usleep(6000);
			curl_multi_exec($mh, $running);
		} while ($running);

		$responseDian = array();
		foreach ($handles as $ch) {
			$result = curl_multi_getcontent($ch);
			$responseDian = json_decode($result, true);
			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
		}
		Log::info($responseDian);
		$data = DB::table('invoice_headers')->where('id', $this->invoice->id)->update(array(
			'status' => 2,
		));

		Log::info('Finaliza');
		return $data;
	}
}
