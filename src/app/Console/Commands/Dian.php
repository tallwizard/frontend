<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\InvoiceDetail;
use App\Models\InvoiceHeader;
use App\Models\InvoiceResponse;
use App\Models\Note;
use App\Models\NoteResponse;
use App\Models\Provider;
use DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait  Dian
{
	public $invoiceId;
	public $invoiceCode;
	public $invoiceClient;
	public $invoiceTotal = 0;
	public $providerId;
	public $testId;
	public $header = array("accept: */*", "Content-Type: application/json");
	public $url = 'http://localhost/sys/src/';

	public function invoice()
	{
		try {
			$data = InvoiceHeader::select('invoice_headers.*', 'payment_methods.name as payment_method')
				->join('payment_methods', 'payment_methods.id', '=', 'invoice_headers.payment_methods_id')
				->where('invoice_headers.status', 1)
				->limit(1)
				->get();
			if ($data->count()) {
				foreach ($data as $key => $value) {
					$date =  new DateTime($value->created_at);
					$this->invoiceCode = $value->code;
					$this->invoiceId = $value->id;
					$this->invoiceClient = $value->clients_id;
					$send = [
						'data' => [
							'proveedor' => $this->getProvider('invoice'),
							'cliente'   => $this->getClient(),
							'numero'    => $value->consecutive,
							'fecha'     => $date->format('Y-m-d'),
							'tipo'      => 3,
							'items'     => $this->getItems('invoice'),
							'testSetId' => $this->testId,
							'cuentaBancaria' => $value->bank_account,
							'metodoPago' => $value->payment_methods_id,
							'nombreMetodoPago' => $value->payment_method,
							'formaPago' => $value->way_payments_id,
							'fechaVencimiento' => $value->expiration_date,
							'descripcion' => $value->description,
							'total' => $this->invoiceTotal,
						],
						'type' => 'invoice'
					];

					$mh = curl_multi_init();
					$ch = curl_init($this->url);
					$handles[] = $ch;

					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($send));
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

					if (!empty($responseDian)) {

						DB::beginTransaction();
						$invoiceResponse = new InvoiceResponse();
						$invoiceResponse->invoice_headers_id = $this->invoiceId;
						if (array_key_exists("mensaje", $responseDian)) {
							$invoiceResponse->message = $responseDian['mensaje'];
						}
						if (array_key_exists("error", $responseDian)) {
							$invoiceResponse->error = $responseDian['error'];
						}
						if (array_key_exists("data", $responseDian)) {
							$invoiceResponse->data_invoice = $responseDian['data'];
						}
						if (array_key_exists("mensCorreo", $responseDian)) {
							$invoiceResponse->mail_message = $responseDian['mensCorreo'];
						}
						if (array_key_exists("correo", $responseDian)) {
							$invoiceResponse->email = $responseDian['correo'];
						}
						if (array_key_exists("CUFE", $responseDian)) {
							$invoiceResponse->cufe = $responseDian['CUFE'];
						}
						if (array_key_exists("NombArch", $responseDian)) {
							$invoiceResponse->file_name = $responseDian['NombArch'];
						}
						if (array_key_exists("CodeQr", $responseDian)) {
							$invoiceResponse->qr = $responseDian['CodeQr'];
						}
						if (array_key_exists("trackId", $responseDian)) {
							$invoiceResponse->track_id = $responseDian['trackId'];
						}
						if (array_key_exists("ruta", $responseDian)) {
							$invoiceResponse->url = $responseDian['ruta'];
						}
						if (array_key_exists("estado", $responseDian)) {
							$status = 1;
							if ($responseDian['estado'] == 'Aceptado') {
								$status = 2;
							} else if ($responseDian['estado'] == 'Error') {
								$status = 3;
							}
							$invoiceResponse->status = $status;
							$invoiceStatus = InvoiceHeader::find($this->invoiceId);
							$invoiceStatus->status = $status;
							$invoiceStatus->save();
						}

						$invoiceResponse->save();
						DB::commit();
					} else {
						DB::rollback();
						return 'No hubo respuesta por parte de la DIAN al guardar facturas';
					}
				}
				return 'Exito al guardar las facturas';
			}
		} catch (QueryException $e) {
			DB::rollback();
			Log::error($e->getMessage());
			return 'Error al guardar las facturas';
		}
	}
	public function note()
	{
		try {
			$data = Note::select('notes.*', 'note_concepts.prefix as concept', 'note_concepts.name as conceptName', 'type_notes.id as type_note', 'invoice_headers.code as invoice_code', 'invoice_headers.clients_id as invoice_clients_id')
				->join('note_concepts', 'note_concepts.id', '=', 'notes.note_concepts_id')
				->join('type_notes', 'type_notes.id', '=', 'note_concepts.type_notes_id')
				->join('invoice_headers', 'invoice_headers.id', '=', 'notes.invoice_headers_id')
				->orderByDesc('notes.created_at')
				->where('notes.status', 1)
				->limit(1)
				->get();
			if ($data->count()) {
				foreach ($data as $key => $value) {
					$date =  new DateTime($value->created_at);
					$this->invoiceCode = $value->invoice_code;
					$this->invoiceId = $value->invoice_headers_id;
					$this->invoiceClient = $value->invoice_clients_id;
					
					$send = [
						'data' => [
							'proveedor' => $this->getProvider('note'),
							'cliente'   => $this->getClient(),
							'prefijo'   => $value->code,
							'numero'    => $value->consecutive,
							'fecha'     => $date->format('Y-m-d'),
							'concepto'    => $value->concept,
							'nombreConcepto'    => $value->conceptName,
							'tipo'      => $value->type_note,
							'descripcion' => $value->description,
							'total'     => $value->total,
							'discrepancia'     => 1,
							'factura'     => $this->getInvoiceResponse(),
							'testSetId' => $this->testId,
						],
						'type' => 'note'
					];
					$mh = curl_multi_init();
					$ch = curl_init($this->url);
					$handles[] = $ch;

					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($send));
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
						$responseDian[] = json_decode($result, true);
						curl_multi_remove_handle($mh, $ch);
						curl_close($ch);
					}
					Log::warning($responseDian);

					DB::beginTransaction();
					$noteResponse = new NoteResponse();
					$noteResponse->notes_id = $value->id;
					if (array_key_exists("mensaje", $responseDian)) {
						$noteResponse->message = $responseDian['mensaje'];
					}
					if (array_key_exists("error", $responseDian)) {
						$noteResponse->error = $responseDian['error'];
					}
					if (array_key_exists("data", $responseDian)) {
						$noteResponse->data_invoice = $responseDian['data'];
					}
					if (array_key_exists("mensCorreo", $responseDian)) {
						$noteResponse->mail_message = $responseDian['mensCorreo'];
					}
					if (array_key_exists("correo", $responseDian)) {
						$noteResponse->email = $responseDian['correo'];
					}
					if (array_key_exists("CUFE", $responseDian)) {
						$noteResponse->cufe = $responseDian['CUFE'];
					}
					if (array_key_exists("NombArch", $responseDian)) {
						$noteResponse->file_name = $responseDian['NombArch'];
					}
					if (array_key_exists("CodeQr", $responseDian)) {
						$noteResponse->qr = $responseDian['CodeQr'];
					}
					if (array_key_exists("trackId", $responseDian)) {
						$noteResponse->track_id = $responseDian['trackId'];
					}
					if (array_key_exists("ruta", $responseDian)) {
						$noteResponse->url = $responseDian['ruta'];
					}
					if (array_key_exists("estado", $responseDian)) {
						$status = 1;
						if ($responseDian['estado'] == 'Aceptado') {
							$status = 2;
						} else if ($responseDian['estado'] == 'Error') {
							$status = 3;
						}
						$noteResponse->status = $status;
						$noteStatus = Note::find($value->id);
						$noteStatus->status = $status;
						$noteStatus->save();
					}
					$noteResponse->save();
					DB::commit();
				}
				return 'Exito al guardar las notas';
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return 'Error al guardar las notas';
		}
	}

	public function getItems($type)
	{
		$items = array();
		try {
			$data = InvoiceDetail::where('invoice_headers_id', $this->invoiceId)->get();
			$totalItem = 0;
			foreach ($data as $key => $value) {

				$items[$key] = [
					'codigo' => $value->code,
					'producto' => $value->name,
					'marca' => $value->brand,
					'cantidad' => $value->amount,
					'unidad' => '94',
					'valorUnidad' => $value->price,
					'iva' => $value->iva,
					'impos' => 0,
					'antesImp' => true,
					'descuento' => $value->discount
				];
				if ($type == 'note') {
					$note = Note::where('invoice_headers_id', $this->invoiceId)->first();
					$notaReset = ($note->total / ($data->count()));
					$detail = InvoiceDetail::find($value->id);
					$detail->amount = 1;
					$detail->price = number_format($notaReset, 2, '.', '');
					$detail->discount = 0;
					$detail->save();
					$totalItem += $notaReset;
				} else {
					$totalItem += (($value->amount * $value->price) - $value->discount);
				}
			}
			$this->invoiceTotal = $totalItem;
			return $items;
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['messagge' => $e->getMessage()], 500);
		}
	}

	public function getClient()
	{
		try {
			$client = array();
			$data = Client::select('clients.*', 'type_documents.id as type_document', 'type_clients.id as type_client', 'departaments.name as departament', 'cities.name as city', 'cities.zip_code as dane')
				->join('type_documents', 'clients.type_documents_id', '=', 'type_documents.id')
				->join('type_clients', 'clients.type_clients_id', '=', 'type_clients.id')
				->join('cities', 'clients.cities_id', '=', 'cities.id')
				->join('departaments', 'cities.departaments_id', '=', 'departaments.id')
				->where('clients.id', $this->invoiceClient)
				->first();
			$document = $this->getNit($data->document, $data->type_document);
			$client['tipoCliente']       = $data->type_client;
			$client['nombre']            = $data->name;
			$client['apellidos']         = $data->last_name;
			$client['documento']         = $document[0];
			$client['digitoVerificacion']    = $document[1];
			$client['tipoDoc']           = $data->type_document;
			$client['listaObligaciones'] = "TIPOS OBLIGACIONES-RESPONSABILIDADES:2016";
			$client['obligaciones']      = $this->obligations();
			$client['telefono']          = $data->phone;
			$client['email']             = $data->email;
			$client['departamento']      = $data->departament;
			$client['ciudad']            = $data->city;
			$client['daneCiudad']        = $data->dane;
			$client['direccion']         = $data->address;
			return $client;
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['messagge' => $e->getMessage()], 500);
		}
	}

	public function getProvider($type)
	{
		try {
			$data = Provider::select('providers.*', 'type_documents.id as type_document', 'type_regimes.id as type_regime', 'departaments.name as departament', 'cities.name as city', 'cities.zip_code as dane', 'resolutions.number as resolution_number', 'resolutions.prefix as resolution_prefix', 'resolutions.key as resolution_key', 'software_data.identification as software_identification', 'software_data.pin as software_pin', 'software_data.test_id as software_test_id', 'resolutions.start_date as resolution_start_date', 'resolutions.end_date as resolution_ending_date', 'resolutions.start_consecutive as resolution_start_consecutive', 'resolutions.end_consecutive as resolution_end_consecutive')
				->join('type_documents', 'providers.type_documents_id', '=', 'type_documents.id')
				->join('type_regimes', 'providers.type_regimes_id', '=', 'type_regimes.id')
				->join('cities', 'providers.cities_id', '=', 'cities.id')
				->join('departaments', 'cities.departaments_id', '=', 'departaments.id')
				->join('institutions', 'institutions.providers_id', '=', 'providers.id')
				->join('dependences', 'dependences.institutions_id', '=', 'institutions.id')
				->join('resolutions', 'resolutions.dependences_id', '=', 'dependences.id')
				->join('software_data', 'software_data.id', '=', 'providers.software_data_id')
				->where([
					['resolutions.code', '=', $this->invoiceCode]
				])
				->first();

			$provider = array();
			$document = $this->getNit($data->document, $data->type_document);
			$this->providerId         = $data->id;
			if ($data->dian_test == 1) {
				$this->testId = $data->software_test_id;
			}
			$provider['tipoCliente']  = $data->type_clients_id;
			$provider['nombre']       = $data->office_name;
			$provider['documento']    = $document[0];
			$provider['digitoVerificacion']   = $document[1];
			$provider['tipoDoc']      = $data->type_document;
			$provider['obligaciones'] = $this->obligations();
			$provider['Regimen']      = $data->type_regime;
			$provider['telefono']     = $data->phone;
			$provider['email']        = $data->email;
			$provider['autoenvio']        = $data->email_autoship;
			$provider['departamento'] = $data->departament;
			$provider['ciudad']       = $data->city;
			$provider['daneCiudad']   = (int) $data->dane;
			$provider['direccion']    = $data->address;

			if ($type == 'note') {
				$provider['nombreRepresentante'] = $data->agent_name;
				$provider['documentoRepresentante'] = $data->agent_document;
			}

			// if ($type == 'invoice') {
			$provider['resolucion']['codigo']     = $data->resolution_number;
			$provider['resolucion']['claveTec']               = $data->resolution_key;
			$provider['resolucion']['SoftwareID']             = $data->software_identification;
			$provider['resolucion']['Software']               = $document[0];
			$provider['resolucion']['PIN']                    = $data->software_pin;
			$provider['resolucion']['prefijo']                = $data->resolution_prefix;
			$provider['resolucion']['fecha']  = $data->resolution_start_date;
			$provider['resolucion']['vence']    = $data->resolution_ending_date;
			$provider['resolucion']['inicio']     = $data->resolution_start_consecutive;
			$provider['resolucion']['hasta']      = $data->resolution_end_consecutive;
			// }
			return $provider;
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['messagge' => $e->getMessage()], 500);
		}
	}

	public function getNit($value, $typeDocument)
	{

		if ($typeDocument != '31') {
			$document = [$value, 0];
		} else {
			$x = 0;
			$y = 0;
			$nit1 = $value;
			$z = strlen($nit1);
			$dv1 = 0;

			$vpri = array(16);
			$vpri[1] = 3;
			$vpri[2] = 7;
			$vpri[3] = 13;
			$vpri[4] = 17;
			$vpri[5] = 19;
			$vpri[6] = 23;
			$vpri[7] = 29;
			$vpri[8] = 37;
			$vpri[9] = 41;
			$vpri[10] = 43;
			$vpri[11] = 47;
			$vpri[12] = 53;
			$vpri[13] = 59;
			$vpri[14] = 67;
			$vpri[15] = 71;
			for ($i = 0; $i < $z; $i++) {
				$y = (substr($nit1, $i, 1));
				$x += ($y * $vpri[$z - $i]);
			}
			$y = $x % 11;
			if ($y > 1) {
				$dv1 = 11 - $y;
			} else {
				$dv1 = $y;
			}
			$document = [$value, $dv1];
		}
		return $document;
	}

	public function obligations()
	{
		$obligation = array();
		$obligation[0]['nombre'] = "Otro tipo de obligado";
		$obligation[0]['codigo'] = "O-99";
		$obligation[0]['listName'] = '49';
		$obligation[0]['identificadorImp'] = '01';
		$obligation[0]['nombreImp'] = 'IVA';
		return $obligation;
	}

	public function getInvoiceResponse()
	{
		$data = array();
		try {
			$invoice = InvoiceResponse::select('resolutions.prefix as prefix', 'invoice_headers.consecutive as consecutive', 'invoice_responses.cufe as cufe', 'invoice_headers.description as description', 'invoice_headers.created_at as created_at')
				->join('invoice_headers', 'invoice_headers.id', '=', 'invoice_responses.invoice_headers_id')
				->join('resolutions', 'resolutions.code', '=', 'invoice_headers.code')
				->where('invoice_responses.invoice_headers_id', $this->invoiceId)
				->first();
			if ($invoice) {
				$date =  new DateTime($invoice->created_at);
				$data = [
					'id'          => $invoice->prefix . ' - ' . $invoice->consecutive,
					'cufe'        => $invoice->cufe,
					'fecha'       => $date->format('Y-m-d'),
					'descripcion' => $invoice->description,
					'items'       => $this->getItems(true),
				];
			} else {
				Log::error('Error al traer los datos de la factura en la nota');
			}
		} catch (QueryException $ex) {
			Log::error('Error al traer los datos de la factura en la nota: ' . $ex);
		}
		return $data;
	}
}
