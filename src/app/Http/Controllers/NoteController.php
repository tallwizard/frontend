<?php

namespace App\Http\Controllers;

use App\Models\InvoiceHeader;
use App\Models\Note;
use App\Models\NoteConcept;
use App\Traits\SequenceTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class NoteController extends Controller
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
	public function delete($id)
	{
		try {
			DB::beginTransaction();
			$data = Note::find($id);
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

	public function loadingNote()
	{
		try {
			$data = Note::select('notes.*', 'note_concepts.name as concept', 'type_notes.name as type_note', DB::raw("CONCAT(invoice_headers.code,' - ',invoice_headers.consecutive) as invoice"))
				->join('note_concepts', 'note_concepts.id', '=', 'notes.note_concepts_id')
				->join('type_notes', 'type_notes.id', '=', 'note_concepts.type_notes_id')
				->join('invoice_headers', 'invoice_headers.id', '=', 'notes.invoice_headers_id')
				->orderByDesc('notes.updated_at')
				->where('notes.status', 1)
				->get();
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen datos'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function getConcept()
	{
		try {
			$data = NoteConcept::all();
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

	public function consultNote(Request $request)
	{
		try {
			$data = Note::select('notes.*', 'note_concepts.name as concept', 'type_notes.name as type_note', DB::raw("CONCAT(invoice_headers.code,' - ',invoice_headers.consecutive) as invoice"), DB::raw("CONCAT(note_responses.url,'/',note_responses.file_name) as url"))
				->join('note_concepts', 'note_concepts.id', '=', 'notes.note_concepts_id')
				->join('type_notes', 'type_notes.id', '=', 'note_concepts.type_notes_id')
				->join('invoice_headers', 'invoice_headers.id', '=', 'notes.invoice_headers_id')
				->join('clients', 'invoice_headers.clients_id', '=', 'clients.id')
				->join('resolutions', 'invoice_headers.code', '=', 'resolutions.code')
				->leftJoin('note_responses', 'note_responses.notes_id', '=', 'notes.id')
				->orderByDesc('notes.updated_at');
			if ($request->dateStart && $request->dateEnd) {
				$data->whereBetween('notes.created_at', [$request->dateStart . ' 00:00:00', $request->dateEnd . ' 23:59:59']);
			} else if ($request->dateStart) {
				$data->whereBetween('notes.created_at', [$request->dateStart . ' 00:00:00', date('Y-m-d') . ' 23:59:59']);
			} else if ($request->dateEnd) {
				$data->whereBetween('notes.created_at', [$request->dateEnd . ' 00:00:00', date('Y-m-d') . ' 23:59:59']);
			}
			if ($request->consecutive) {
				$data->where('notes.consecutive', $request->consecutive);
			}
			if ($request->client) {
				$data->where('clients.document', $request->client);
			}
			if ($request->prefixInvoice) {
				$data->where('invoice_headers.code', $request->prefixInvoice);
			}
			if ($request->consecutiveInvoice) {
				$data->where('invoice_headers.consecutive', $request->consecutiveInvoice);
			}
			if ($request->consecutiveNote) {
				$data->where('notes.consecutive', $request->consecutiveNote);
			}
			$data = $data->get();

			if ($data->count()) {
				return response()->json(['data' => $data], 200);
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
			$rules = [
				'prefixInvoice' => ['required', 'string', 'exists:App\Models\InvoiceHeader,code'],
				'consecutive' => ['required', 'numeric'],
				'concept' => ['required', 'string', 'exists:App\Models\NoteConcept,id'],
			];

			if ($request->concept != 2) {
				$rules += ['total' => ['required', 'numeric', 'digits_between:1,200']];
			}
			$validator = Validator::make($request->all(), $rules, $messages = [
				'prefixInvoice.required' => 'El prefijo de la factura es requerido',
				'prefixInvoice.string' => 'El prefijo de la factura no es valido',
				'prefixInvoice.exists' => 'El prefijo de la factura no existe',
				'consecutive.required' => 'El consecutivo es requerido',
				'consecutive.numeric' => 'El consecutivo es requerido',
				'concept.required' => 'El concepto es requerido',
				'concept.string' => 'El concepto no es valido',
				'concept.exists' => 'El concepto no existe',
				'total.required' => 'El total es requerido',
				'total.numeric' => 'El total debe ser numerico',
				'total.digits_between' => 'El total tiene un maximo de :max y un minimo de :min caracteres',
			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}

			$validationConcept = NoteConcept::where('id', $request->concept)->first('type_notes_id');
			$validationInvoice = InvoiceHeader::where([['code', '=', $request->prefixInvoice], ['consecutive', '=', $request->consecutive]])->first('balance');
			if ($validationConcept->type_notes_id == 1) {
				if ($validationInvoice->balance <= 0) {
					return response()->json(['message' => 'El saldo de la factura es: ' . $validationInvoice->balance], 400);
				} else if ($request->total > $validationInvoice->balance && $request->concept != 2) {
					return response()->json(['message' => 'El total de la nota supera el saldo, el saldo de la factura es: ' . $validationInvoice->balance], 400);
				}
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json('Server Error', 500);
		}
	}

	public function create(Request $request)
	{
		try {
			$validationConcept = NoteConcept::where('id', $request->concept)->first('type_notes_id');
			$validationInvoice = InvoiceHeader::where([['code', '=', $request->prefixInvoice], ['consecutive', '=', $request->consecutive]])->first();
			if ($validationConcept->type_notes_id == 1) {
				if ($validationInvoice->balance <= 0) {
					return response()->json(['message' => 'El saldo de la factura es: ' . $validationInvoice->balance], 400);
				} else if ($request->total > $validationInvoice->balance && $request->concept != 2) {
					return response()->json(['message' => 'El total de la nota supera el saldo, el saldo de la factura es: ' . $validationInvoice->balance], 400);
				}
				if ($request->concept != 2) {
					$total = ($validationInvoice->balance -  $request->total);
					$totalNote = $request->total;
				} else {
					$total = $validationInvoice->balance;
					$totalNote = $total;
				}
			} else if ($validationConcept->type_notes_id == 2) {
				$total = ($request->total + $validationInvoice->balance);
				$totalNote = $request->total;
			}
			$secuence = $this->setSequence($request->prefix);

			$data = new Note();
			$data->invoice_headers_id = $validationInvoice->id;
			$data->code = $request->prefix;
			$data->consecutive = $secuence['value'];
			$data->description = $request->description;
			$data->note_concepts_id = $request->concept;
			$data->status = 1;
			$data->total = $totalNote;
			$data->users_id = $request->user()->id;
			$data->save();
			DB::commit();
			if ($data) {
				$invoice = InvoiceHeader::find($validationInvoice->id);
				if ($request->concept == 2) {
					$total = 0;
				}
				$invoice->balance = $total;
				$invoice->save();
				if ($invoice) {
					return response()->json(['data' => 'Nota ' . $data->code . '-' . $data->consecutive . ' creada correctamente'], 200);
				}
			}
		} catch (QueryException $e) {
			DB::rollBack();
			Log::error($e->getMessage());
			return response()->json('Server Error', 500);
		}
	}
}
