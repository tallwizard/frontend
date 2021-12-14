<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceImportRequest;
use App\Http\Requests\InvoiceRequest;
use App\Models\Client;
use App\Models\InvoiceDetail;
use App\Models\InvoiceHeader;
use App\Models\PaymentMethod;
use App\Models\Resolution;
use App\Models\WayPayment;
use App\Traits\SequenceTrait;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class InvoiceController extends Controller
{
	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			if (Auth::user()->active != true) {
				return response()->json(['message' => 'El usuario no se encuentra activo'], 401);
			}
			return $next($request);
		});
	}
	use SequenceTrait;
	public function index()
	{
		dd(database_path());
		try {
			$data = InvoiceHeader::select('invoice_headers.invoice_code', 'invoice_headers.invoice_number', 'invoice_headers.total', 'invoice_headers.created_at', 'invoice_headers.dian_status', 'invoice_headers.description', DB::raw("CONCAT(clients.name,' ',clients.last_name,' - ',clients.document) as client"), 'dependences.name as dependence', 'institutions.name as institution')
				->join('clients', 'invoice_headers.clients_id', '=', 'clients.id')
				->join('resolutions', 'invoice_headers.resolutions_id', '=', 'resolutions.id')
				->join('dependences', 'invoice_headers.dependences_id', '=', 'dependences.id')
				->join('institutions', 'dependences.institutions_id', '=', 'institutions.id')
				->orderByDesc('invoice_headers.created_at')
				->get();
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json('Not Found', 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json('Server Error', 500);
		}
	}

	public function getBalance(Request $request)
	{
		try {
			$data = InvoiceHeader::where([['code', '=', $request->prefix], ['consecutive', '=', $request->consecutive]])->first();
			return response()->json(['data' => $data], 200);
		} catch (QueryException $ex) {
			Log::error($ex);
			return response()->json('Server Error', 500);
		}
	}

	public function printFile(Request $request)
	{
		$data = config('app.url_api');
		return redirect()->away($data . $request->file);
	}

	public function consultDetail($id)
	{
		try {
			$data = InvoiceDetail::where('invoice_headers_id', $id)->get();
			if ($data->count()) {
				foreach ($data as $key => $value) {
					$response[$key] = [
						'productCode' => $value->code,
						'productName' => $value->name,
						'productBrand' => $value->brand,
						'productAmount' => number_format($value->amount, 2, ',', '.'),
						'productPrice' => '$ ' . number_format($value->price, 2, ',', '.'),
						'productDiscount' => '$ ' . number_format($value->discount, 2, ',', '.')
					];
				}
				return response()->json(['data' => $response], 200);
			} else {
				return response()->json(['message' => 'No existen datos'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function consultInvoice(Request $request)
	{
		try {
			$response = array();
			$responseItems = array();
			$data = DB::table('invoice_headers')->select('invoice_headers.*', DB::raw("CONCAT(invoice_responses.url,'/',invoice_responses.file_name) as url"), DB::raw("CONCAT(clients.name,' ',clients.last_name,' - ',clients.document) as client"), 'dependences.name as dependence', 'institutions.name as institution', 'way_payments.name as wayPay', 'payment_methods.name as payMethod')
				->leftJoin('invoice_responses', 'invoice_responses.invoice_headers_id', '=', 'invoice_headers.id')
				->join('clients', 'invoice_headers.clients_id', '=', 'clients.id')
				->join('resolutions', 'invoice_headers.code', '=', 'resolutions.code')
				->join('dependences', 'resolutions.dependences_id', '=', 'dependences.id')
				->join('institutions', 'dependences.institutions_id', '=', 'institutions.id')
				->join('way_payments', 'way_payments.id', '=', 'invoice_headers.way_payments_id')
				->join('payment_methods', 'payment_methods.id', '=', 'invoice_headers.payment_methods_id')
				->orderByDesc('invoice_headers.updated_at');

			if ($request->dateStart && $request->dateEnd) {
				$data->whereBetween('invoice_headers.created_at', [$request->dateStart . ' 00:00:00', $request->dateEnd . ' 23:59:59']);
			} else if ($request->dateStart) {
				$data->whereBetween('invoice_headers.created_at', [$request->dateStart . ' 00:00:00', date('Y-m-d') . ' 23:59:59']);
			} else if ($request->dateEnd) {
				$data->whereBetween('invoice_headers.created_at', [$request->dateEnd . ' 00:00:00', date('Y-m-d') . ' 23:59:59']);
			}
			if ($request->consecutive) {
				$data->where('invoice_headers.consecutive', $request->consecutive);
			}
			if ($request->client) {
				$data->where('clients.document', $request->client);
			}
			if ($request->prefix) {
				$data->where('invoice_headers.code', $request->prefix);
			}

			$data = $data->get();

			if ($data->count()) {
				foreach ($data as $key => $value) {
					$response[$key] = [
						'id' => $value->id,
						'prefix' => $value->code . ' - ' . $value->consecutive,
						'description' => $value->description,
						'client' => $value->client,
						'expirationDate' => $value->expiration_date,
						'wayPay' => $value->wayPay,
						'payMethod' => $value->payMethod,
						'bankAccount' => $value->bank_account,
						'total' => '$ ' . number_format($value->total, 2, ',', '.'),
						'status' => $value->status,
						'items' => $responseItems,
						'url' => $value->url
					];
				}
				return response()->json(['data' => $response], 200);
			} else {
				return response()->json(['message' => 'No existen datos'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function getClient(Request $request, $document = null)
	{
		try {
			$data = Client::where('document', $document)->first('id');
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json('Not Found', 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage);
			return response()->json('Server Error', 500);
		}
	}

	public function importInvoice(Request $request)
	{
		$data = $request->all();
		$consecutiveInvoices = array();
		$total = 0;
		foreach ($data as $key => $valueInvoice) {
			try {
				$prefix = mb_strtoupper($valueInvoice['prefix'], 'UTF-8');
				$date =  new DateTime($valueInvoice['expirationDate']);
				$secuence = $this->setSequence($prefix);
				$client = Client::where('document', $valueInvoice['client'])->first('id');
				$header = new InvoiceHeader();
				$header->code = $prefix;
				$header->consecutive = $secuence['value'];
				$header->description = $valueInvoice['description'];
				$header->clients_id = $client->id;
				$header->expiration_date = $date->format('Y-m-d');
				$header->payment_methods_id = $valueInvoice['payMethod'];
				$header->way_payments_id = $valueInvoice['wayPay'];
				if (key_exists('bankAccount', $data)) {
					$header->bank_account = $valueInvoice['bankAccount'];
				}
				$header->total = $valueInvoice['total'];
				$header->balance = $valueInvoice['total'];
				$header->users_id = $request->user()->id;
				$header->save();
			} catch (QueryException $e) {
				Log::error($e->getMessage());
				return response()->json('Server Error', 500);
			}
			array_push($consecutiveInvoices, $prefix . ' - ' . $secuence['value']);
			try {
				if ($header) {
					foreach ($valueInvoice['items'] as $key => $valueItems) {
						$detail = new InvoiceDetail();
						$detail->invoice_headers_id = $header->id;
						$detail->code = $valueItems['productCode'];
						$detail->name = $valueItems['productName'];
						if (key_exists('productBrand', $valueItems)) {
							$detail->brand = $valueItems['productBrand'];
						}
						$detail->amount = $valueItems['productAmount'];
						$detail->price = $valueItems['productPrice'];
						if (key_exists('productDiscount', $valueItems)) {
							$detail->discount = $valueItems['productDiscount'];
						}
						if (key_exists('productReasonDiscount', $valueItems)) {
							$detail->reason_discount = $valueItems['productReasonDiscount'];
						}
						$detail->iva = 0;
						$detail->save();
					}
				}
			} catch (QueryException $e) {
				Log::error($e->getMessage());
				return response()->json('Server Error', 500);
			}
		}
		return response()->json(['data' => $consecutiveInvoices], 200);
	}

	public function create(Request $request)
	{
		try {
			$requestHeader = $request->header;
			DB::beginTransaction();
			$date =  new DateTime($requestHeader['expirationDate']);
			$secuence = $this->setSequence($requestHeader['prefix']);
			$client = Client::where('document', $requestHeader['client'])->first('id');
			$header = new InvoiceHeader();
			$header->code = $requestHeader['prefix'];
			$header->consecutive = $secuence['value'];
			$header->description = $requestHeader['description'];
			$header->clients_id = $client->id;
			$header->expiration_date = $date->format('Y-m-d');
			$header->payment_methods_id = $requestHeader['payMethod'];
			$header->way_payments_id = $requestHeader['wayPay'];
			if ($requestHeader['bankAccount']) {
				$header->bank_account = $requestHeader['bankAccount'];
			}
			$header->total = $requestHeader['total'];
			$header->balance = $requestHeader['total'];
			$header->users_id = $request->user()->id;
			$header->save();
			if ($header) {
				foreach ($request->items as $key => $value) {
					$detail = new InvoiceDetail();
					$detail->invoice_headers_id = $header->id;
					$detail->code = $value['productCode'];
					$detail->name = $value['productName'];
					if (key_exists('productBrand', $value)) {
						$detail->brand = $value['productBrand'];
					}
					$detail->amount = $value['productAmount'];
					$detail->price = $value['productPrice'];
					if (key_exists('productDiscount', $value)) {
						$detail->discount = $value['productDiscount'];
					}
					if (key_exists('productReasonDiscount', $value)) {
						$detail->reason_discount = $value['productReasonDiscount'];
					}
					$detail->iva = 0;
					$detail->save();
				}
			}
			DB::commit();
			if ($detail) {
				return response()->json(['data' => $header->code . '-' . $secuence['value']], 200);
			}
		} catch (QueryException $e) {
			DB::rollBack();
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error al guardar los datos'], 500);
		}
	}

	public function getPaymentMethods()
	{
		try {
			$data = PaymentMethod::orderBy('name')->get();
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json('Not Found', 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage);
			return response()->json('Server Error', 500);
		}
	}

	public function getPrefix()
	{
		try {
			$data = Resolution::where('active', true)->orderBy('code')->get('code');
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json('Not Found', 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage);
			return response()->json('Server Error', 500);
		}
	}

	public function getWayPayments()
	{
		try {
			$data = WayPayment::select(['id', 'name'])->orderBy('name')->get();
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json('Not Found', 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage);
			return response()->json('Server Error', 500);
		}
	}

	public function loadingInvoice()
	{
		try {
			$response = array();
			$responseItems = array();
			$data = InvoiceHeader::select('invoice_headers.*', 'invoice_responses.status as status', DB::raw("CONCAT(clients.name,' ',clients.last_name,' - ',clients.document) as client"), 'dependences.name as dependence', 'institutions.name as institution', 'way_payments.name as wayPay', 'payment_methods.name as payMethod')
				->join('clients', 'invoice_headers.clients_id', '=', 'clients.id')
				->join('resolutions', 'invoice_headers.code', '=', 'resolutions.code')
				->join('dependences', 'resolutions.dependences_id', '=', 'dependences.id')
				->join('institutions', 'dependences.institutions_id', '=', 'institutions.id')
				->join('way_payments', 'way_payments.id', '=', 'invoice_headers.way_payments_id')
				->join('invoice_responses', 'invoice_responses.invoice_headers_id', '=', 'invoice_headers.id')
				->join('payment_methods', 'payment_methods.id', '=', 'invoice_headers.payment_methods_id')
				->orderByDesc('invoice_headers.updated_at')
				->orWhereDate('invoice_responses.updated_at',  Carbon::today())
				->orWhere('invoice_headers.status', 1)
				->orWhere('invoice_responses.status', 3)
				->get();
			if ($data->count()) {
				foreach ($data as $key => $value) {
					$response[$key] = [
						'id' => $value->id,
						'prefix' => $value->code . ' - ' . $value->consecutive,
						'description' => $value->description,
						'client' => $value->client,
						'expirationDate' => $value->expiration_date,
						'wayPay' => $value->wayPay,
						'payMethod' => $value->payMethod,
						'bankAccount' => $value->bank_account,
						'total' => '$ ' . number_format($value->total, 2, ',', '.'),
						'status' => $value->status,
						'items' => $responseItems
					];
				}
				return response()->json(['data' => $response], 200);
			} else {
				return response()->json(['message' => 'No existen datos'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function validateInvoice(Request $request)
	{
		try {
			$errors = array();
			foreach ($request->all() as $key => $value) {

				$validator = Validator::make($value, [
					'prefix' => ['required', 'exists:App\Models\Resolution,code'],
					'client' => ['required', 'exists:App\Models\Client,document'],
					'expirationDate' => ['required', 'date',],
					'description' => ['required', 'string', 'max:1000'],
					'wayPay' => ['required', 'exists:App\Models\WayPayment,id'],
					'payMethod' => ['required', 'exists:App\Models\PaymentMethod,id'],
					'bankAccount' => ['nullable', 'string', 'max:200'],
					'items' => ['required'],
				], [
					'prefix.required' => 'El prefijo es requerido en la fila: ' . ($key + 1),
					'prefix.exists' => 'El prefijo no existe en la fila: ' . ($key + 1),
					'client.required' => 'El tercero es requerido en la fila: ' . ($key + 1),
					'client.exists' => 'El tercero no existe en la fila: ' . ($key + 1),
					'expirationDate.required' => 'La fecha de vencimiento es requerida en la fila: ' . ($key + 1),
					'expirationDate.date' => 'La fecha de vencimiento no es valida en la fila: ' . ($key + 1),
					'description.required' => 'La descripcion es requerida en la fila: ' . ($key + 1),
					'description.string' => 'La descripcion debe ser texto en la fila: ' . ($key + 1),
					'description.max' => 'La descripcion tiene un maximo de :max caracteres en la fila: ' . ($key + 1),
					'wayPay.required' => 'La forma de pago es requerida en la fila: ' . ($key + 1),
					'wayPay.exists' => 'La forma de pago no existe en la fila: ' . ($key + 1),
					'payMethod.required' => 'El metodo de pago es requerida en la fila: ' . ($key + 1),
					'payMethod.exists' => 'El metodo de pago no existe en la fila: ' . ($key + 1),
					'bankAccount.string' => 'La cuenta bancaria debe ser texto en la fila: ' . ($key + 1),
					'bankAccount.nullable' => 'La cuenta bancaria no es requerida en la fila: ' . ($key + 1),
					'bankAccount.max' => 'La cuenta bancaria tiene un maximo de :max caracteres en la fila: ' . ($key + 1),
					'items.required' => 'El datalle es requerido en la fila: ' . ($key + 1),
				]);

				if ($err = $validator->errors()->all()) {
					foreach ($err as $keyErr => $valueErr) {
						array_push($errors, $valueErr);
					}
				}
				if ($resolution = Resolution::where('code', $value['prefix'])->first()) {
					if ($resolution->active != true) {
						array_push($errors, ['El prefijo: ' . $resolution->code . ' se encuentra inactivo']);
					}
					if ($resolution->start_date > date(now())) {
						array_push($errors, ['El prefijo: ' . $resolution->code . ', aun no se encuentra habilitado hasta la fecha: ' . $resolution->start_date]);
					} else if ($resolution->end_date < date(now())) {
						array_push($errors, ['El prefijo: ' . $resolution->code . ', se encontraba habilitado hasta la fecha: ' . $resolution->end_date]);
					}
					$invoiceConsecutive = $this->getSequence($resolution->code);
					if ($invoiceConsecutive['value'] >= $resolution->end_consecutive) {
						array_push($errors, ['El prefijo: ' . $resolution->code . ', llego a su consecutivo maximo habilitado: ' . $resolution->end_consecutive]);
					}
				}

				foreach ($value['items'] as $keyItems => $valueItems) {
					$validator = Validator::make($valueItems, [
						'productAmount' => ['required', 'numeric', 'min:1'],
						'productPrice' => ['required', 'numeric', 'min:1'],
						'productDiscount' => ['numeric', 'min:0', 'nullable'],
					], [
						'productAmount.required' => 'La cantidad de item es requerido en la fila: ' . ($keyItems + 1),
						'productAmount.numeric' => 'La cantidad de item debe ser numerico en la fila: ' . ($keyItems + 1),
						'productAmount.min' => 'La cantidad de item tiene un minimo de :min en la fila: ' . ($keyItems + 1),
						'productPrice.required' => 'El precio de item es requerido en la fila: ' . ($keyItems + 1),
						'productPrice.numeric' => 'El precio de item debe ser numerico en la fila: ' . ($keyItems + 1),
						'productPrice.min' => 'El precio de item tiene un minimo de :min en la fila: ' . ($keyItems + 1),
						'productDiscount.nullable' => 'El descuento de item no es requerido en la fila: ' . ($keyItems + 1),
						'productDiscount.numeric' => 'El descuento de item debe ser numerico en la fila: ' . ($keyItems + 1),
						'productDiscount.min' => 'El descuento de item tiene un minimo de :min en la fila: ' . ($keyItems + 1),
					]);

					if ($err = $validator->errors()->all()) {
						foreach ($err as $keyErr => $valueErr) {
							array_push($errors, $valueErr);
						}
					}
				}
				if (!empty($errors)) {
					return response()->json(['data' => $errors], 400);
				}

				foreach ($value['items'] as $keyItems => $valueItems) {
					$rulesItems = [
						'productCode' => ['required', 'string', 'max:50'],
						'productName' => ['required', 'string', 'max:500'],
						'productBrand' => ['nullable', 'string', 'max:100'],
						'productAmount' => ['required', 'numeric', 'min:1'],
						'productPrice' => ['required', 'numeric', 'min:1'],
						'productDiscount' => ['numeric', 'min:0', 'nullable'],
					];

					if ($valueItems['productDiscount'] > 0) {
						$discountItem = $valueItems['productAmount'] * $valueItems['productPrice'];
						$rulesItems['productDiscount'] =  ['numeric', 'min:0', 'nullable', 'max:' . $discountItem];
						$rulesItems['productReasonDiscount'] = ['required', 'string', 'max:200'];
					} else {
						$rulesItems['productReasonDiscount'] = ['nullable', 'string', 'max:200'];
						$rulesItems['productDiscount'] =  ['numeric', 'min:0', 'nullable'];
					}

					$validator = Validator::make($valueItems, $rulesItems, [
						'productCode.required' => 'El codigo de item debe ser requerido en la fila: ' . ($keyItems + 1),
						'productCode.string' => 'El codigo de item debe ser texto en la fila: ' . ($keyItems + 1),
						'productCode.max' => 'El codigo de item tiene un maximo de :max caracteres en la fila: ' . ($keyItems + 1),
						'productName.required' => 'El nombre de item debe ser requerido en la fila: ' . ($keyItems + 1),
						'productName.string' => 'El nombre de item debe ser texto en la fila: ' . ($keyItems + 1),
						'productName.max' => 'El nombre de item tiene un maximo de :max caracteres en la fila: ' . ($keyItems + 1),
						'productBrand.nullable' => 'La marca de item no es requerido en la fila: ' . ($keyItems + 1),
						'productBrand.string' => 'La marca de item debe ser texto en la fila: ' . ($keyItems + 1),
						'productBrand.max' => 'La marca de item tiene un maximo de :max caracteres en la fila: ' . ($keyItems + 1),
						'productAmount.required' => 'La cantidad de item es requerido en la fila: ' . ($keyItems + 1),
						'productAmount.numeric' => 'La cantidad de item debe ser numerico en la fila: ' . ($keyItems + 1),
						'productAmount.min' => 'La cantidad de item tiene un minimo de :min en la fila: ' . ($keyItems + 1),
						'productPrice.required' => 'El precio de item es requerido en la fila: ' . ($keyItems + 1),
						'productPrice.numeric' => 'El precio de item debe ser numerico en la fila: ' . ($keyItems + 1),
						'productPrice.min' => 'El precio de item tiene un minimo de :min en la fila: ' . ($keyItems + 1),
						'productDiscount.nullable' => 'El descuento de item no es requerido en la fila: ' . ($keyItems + 1),
						'productDiscount.numeric' => 'El descuento de item debe ser numerico en la fila: ' . ($keyItems + 1),
						'productDiscount.min' => 'El descuento de item tiene un minimo de :min en la fila: ' . ($keyItems + 1),
						'productDiscount.max' => 'El descuento de item tiene un maximo de :max en la fila: ' . ($keyItems + 1),
						'productReasonDiscount.required' => 'La razon del descuento debe ser requerido en la fila: ' . ($keyItems + 1),
						'productReasonDiscount.string' => 'La razon del descuento debe ser texto en la fila: ' . ($keyItems + 1),
						'productReasonDiscount.max' => 'La razon del descuento tiene un maximo de :max caracteres en la fila: ' . ($keyItems + 1),
					]);
					if ($err = $validator->errors()->all()) {
						foreach ($err as $keyErr => $valueErr) {
							array_push($errors, $valueErr);
						}
					}
				}
			}
			if (!empty($errors)) {
				return response()->json(['data' => $errors], 400);
			} else {
				return response()->json(['message' => 'Terceros sin errores'], 200);
			}
		} catch (\Throwable $th) {
			Log::info($th->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function delete($id)
	{
		try {
			DB::beginTransaction();
			$data = InvoiceHeader::find($id);
			$data->delete();
			DB::commit();
			if ($data) {
				return response()->json(['message' => 'Eliminado con exito'], 200);
			}
		} catch (QueryException $e) {
			DB::rollback();
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}
}
