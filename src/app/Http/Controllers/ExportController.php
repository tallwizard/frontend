<?php

namespace App\Http\Controllers;

use App\Exports\InvoiceExport;
use App\Exports\NoteExport;
use App\Models\InvoiceHeader;
use App\Models\Note;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
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
	public function exportInvoice(Request $request)
	{
		try {
			if ($request->type == 'excel') {
				$filename = 'invoices.xlsx';
				$path = storage_path('app/public/' . $filename);
				Excel::store(new InvoiceExport($request->all()), 'public/' . $filename);
				return Response::make(file_get_contents($path), 200, [
					'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'Content-Disposition' => 'inline; filename="' . $filename . '"'
				]);
			} else if ($request->type == 'pdf') {
				$response = array();
				$data = DB::table('invoice_headers')->select('invoice_headers.*', DB::raw("CONCAT(invoice_responses.url,'/',invoice_responses.file_name) as url"), DB::raw("CONCAT(clients.name,' ',clients.last_name,' - ',clients.document) as client"), 'dependences.name as dependence', 'institutions.name as institution', 'way_payments.name as wayPay', 'payment_methods.name as payMethod', 'providers.office_name as providerName', 'providers.document as providerDocument', 'providers.address as providerAddress', 'providers.phone as providerPhone', 'providers.email as providerEmail')
					->leftJoin('invoice_responses', 'invoice_responses.invoice_headers_id', '=', 'invoice_headers.id')
					->join('clients', 'invoice_headers.clients_id', '=', 'clients.id')
					->join('resolutions', 'invoice_headers.code', '=', 'resolutions.code')
					->join('dependences', 'resolutions.dependences_id', '=', 'dependences.id')
					->join('institutions', 'dependences.institutions_id', '=', 'institutions.id')
					->join('providers', 'institutions.providers_id', '=', 'providers.id')
					->join('way_payments', 'way_payments.id', '=', 'invoice_headers.way_payments_id')
					->join('payment_methods', 'payment_methods.id', '=', 'invoice_headers.payment_methods_id')
					->orderByDesc('invoice_headers.updated_at');

				if ($request->dateStart && $request->dateEnd) {
					$data->whereBetween('invoice_headers.updated_at', [$request->dateStart . ' 00:00:00', $request->dateEnd . ' 23:59:59']);
				} else if ($request->dateStart) {
					$data->whereDate('invoice_headers.updated_at',  date($request->dateStart));
				} else if ($request->dateEnd) {
					$data->whereDate('invoice_headers.updated_at',  date($request->dateEnd));
				}
				if ($request->consecutive) {
					$data->where('invoice_headers.consecutive', $request->consecutive);
				}
				if ($request->client) {
					$data->where('clients.document', $request->client);
				}
				if ($request->prefix) {
					if (is_numeric($request->prefix)) {
						$data->where('resolutions.prefix', $request->prefix);
					} else {
						$data->where('invoice_headers.code', $request->prefix);
					}
				}

				$data = $data->get();

				if ($data->count()) {

					foreach ($data as $key => $value) {
						$date =  new DateTime($value->updated_at);
						$response[$key] = [
							'id' => $value->id,
							'prefix' => $value->code . ' - ' . $value->consecutive,
							'description' => $value->description,
							'client' => $value->client,
							'expirationDate' => $value->expiration_date,
							'wayPay' => $value->wayPay,
							'payMethod' => $value->payMethod,
							'institution' => $value->institution,
							'dependence' => $value->dependence,
							'bankAccount' => $value->bank_account,
							'status' => $value->status,
							'date' => $date->format('Y-m-d'),
							'total' => $value->total,
						];
						$provider = [
							'providerName' => $value->providerName,
							'providerDocument' => $value->providerDocument,
							'providerAddress' => $value->providerAddress,
							'providerPhone' => $value->providerPhone,
							'providerEmail' => $value->providerEmail,
						];
					}
					$dataResult = [
						'imgLogo' => storage_path('app/logoUT.png'),
						'provider' => $provider,
						'data' => $response
					];
					$filename = 'facturas.pdf';
					$path = storage_path('app/public/' . $filename);
					\PDF::loadView('pdf', $dataResult)->setPaper('legal', 'landscape')->save($path);

					return Response::make(file_get_contents($path), 200, [
						'Content-Type' => 'application/pdf',
						'Content-Disposition' => 'inline; filename="' . $filename . '"'
					]);
				} else {
					return response()->json(['message' => 'No existen datos'], 404);
				}
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}
	public function exportNote(Request $request)
	{
		try {
			if ($request->type == 'excel') {
				$filename = 'notes.xlsx';
				$path = storage_path('app/public/' . $filename);
				Excel::store(new NoteExport($request->all()), 'public/' . $filename);
				return Response::make(file_get_contents($path), 200, [
					'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'Content-Disposition' => 'inline; filename="' . $filename . '"'
				]);
			} else if ($request->type == 'pdf') {
				$response = array();
				$data = DB::table('notes')->select('notes.*', 'note_concepts.name as concept', 'type_notes.name as type_note', DB::raw("CONCAT(invoice_headers.code,' - ',invoice_headers.consecutive) as invoice"), DB::raw("CONCAT(note_responses.url,'/',note_responses.file_name) as url"), 'providers.office_name as providerName', 'providers.document as providerDocument', 'providers.address as providerAddress', 'providers.phone as providerPhone', 'providers.email as providerEmail')
					->join('note_concepts', 'note_concepts.id', '=', 'notes.note_concepts_id')
					->join('type_notes', 'type_notes.id', '=', 'note_concepts.type_notes_id')
					->join('invoice_headers', 'invoice_headers.id', '=', 'notes.invoice_headers_id')
					->join('resolutions', 'invoice_headers.code', '=', 'resolutions.code')
					->join('dependences', 'resolutions.dependences_id', '=', 'dependences.id')
					->join('institutions', 'dependences.institutions_id', '=', 'institutions.id')
					->join('providers', 'institutions.providers_id', '=', 'providers.id')
					->leftJoin('note_responses', 'note_responses.notes_id', '=', 'notes.id')
					->orderByDesc('notes.updated_at');
				if ($request->dateStart && $request->dateEnd) {
					$data->whereBetween('notes.updated_at', [$request->dateStart . ' 00:00:00', $request->dateEnd . ' 23:59:59']);
				} else if ($request->dateStart) {
					$data->whereDate('notes.updated_at',  date($request->dateStart));
				} else if ($request->dateEnd) {
					$data->whereDate('notes.updated_at',  date($request->dateEnd));
				}
				if ($request->consecutive) {
					$data->where('notes.consecutive', $request->consecutive);
				}
				if ($request->client) {
					$data->where('clients.document', $request->client);
				}
				if ($request->prefixInvoice) {
					if (is_numeric($request->prefix)) {
						$data->where('resolutions.prefix', $request->prefixInvoice);
					} else {
						$data->where('invoice_headers.code', $request->prefixInvoice);
					}
				}
				if ($request->consecutiveInvoice) {
					$data->where('invoice_headers.consecutive', $request->consecutiveInvoice);
				}
				if ($request->consecutiveNote) {
					$data->where('notes.consecutive', $request->consecutiveNote);
				}
				$data = $data->get();

				if ($data->count()) {
					foreach ($data as $key => $value) {
						$response[$key] = [
							'id' => $value->id,
							'code' => $value->code,
							'consecutive' => $value->consecutive,
							'description' => $value->description,
							'type' => $value->type_note,
							'concept' => $value->concept,
							'total' => $value->total,
							'invoice' => $value->invoice,
							'status' => $value->status,
						];
						$provider = [
							'providerName' => $value->providerName,
							'providerDocument' => $value->providerDocument,
							'providerAddress' => $value->providerAddress,
							'providerPhone' => $value->providerPhone,
							'providerEmail' => $value->providerEmail,
						];
					}
					$dataResult = [
						'imgLogo' => storage_path('app/logoUT.png'),
						'provider' => $provider,
						'data' => $response
					];
					$filename = 'notas.pdf';
					$path = storage_path('app/public/' . $filename);

					\PDF::loadView('notePDF', $dataResult)->setPaper('legal', 'landscape')->save($path);

					return Response::make(file_get_contents($path), 200, [
						'Content-Type' => 'application/pdf',
						'Content-Disposition' => 'inline; filename="' . $filename . '"'
					]);
				} else {
					return response()->json(['message' => 'No existen datos'], 404);
				}
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}
}
