<?php

namespace App\Http\Controllers;

use App\Models\InvoiceHeader;
use App\Models\Resolution;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class ResolutionController extends Controller
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
	public function index($id = null)
	{
		try {
			if ($id) {
				$data = Resolution::where('id', $id)->first();
			} else {
				$data = Resolution::select('resolutions.*', 'dependences.name as dependence')
					->join('dependences', 'dependences.id', '=', 'resolutions.dependences_id')
					->get();
			}
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen resoluciones'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}
	public function create(Request $request)
	{
		try {
			$rules = [
				'code' => ['required', 'string', 'max:10', 'min:3', 'unique:App\Models\Resolution,code'],
				'number' => ['required', 'string', 'max:200'],
				'key' => ['required', 'string', 'max:200'],
				'startDate' => ['required', 'date'],
				'endDate' => ['required', 'date'],
				'startConsecutive' => ['required', 'numeric', 'digits_between:1,10'],
				'endConsecutive' => ['required', 'numeric', 'digits_between:1,10'],
				'prefix' => ['required', 'string', 'max:10'],
				'dependence' => ['required', 'exists:App\Models\Dependence,id'],
			];

			$validator = Validator::make($request->all(), $rules, $messages = [
				'code.required' => 'El codigo es requerido',
				'code.string' => 'El codigo debe ser texto',
				'code.max' => 'El codigo tiene un maximo de :max caracteres',
				'code.min' => 'El codigo tiene un minimo de :min caracteres',
				'code.unique' => 'El codigo ya se encuentra registrado',
				'number.required' => 'El numero es requerido',
				'number.string' => 'El numero debe ser texto',
				'number.max' => 'El numero tiene un maximo de :max caracteres',
				'key.required' => 'La llave es requerida',
				'key.string' => 'La llave debe ser texto',
				'key.max' => 'La llave tiene un maximo de :max caracteres',
				'startDate.required' => 'La fecha inicial es requerida',
				'startDate.date' => 'La fecha inicial no es una fecha valida',
				'endDate.required' => 'La fecha final es requerida',
				'endDate.date' => 'La fecha final no es una fecha valida',
				'startConsecutive.required' => 'El consecutivo inicial es requerido',
				'startConsecutive.numeric' => 'El consecutivo inicial debe ser numerico',
				'startConsecutive.digits_between' => 'El consecutivo inicial tiene un maximo de :max y un minimo de :min caracteres',
				'endConsecutive.required' => 'El consecutivo final es requerido',
				'endConsecutive.numeric' => 'El consecutivo final debe ser numerico',
				'endConsecutive.digits_between' => 'El consecutivo final tiene un maximo de :max y un minimo de :min caracteres',
				'prefix.required' => 'El prefijo es requerido',
				'prefix.string' => 'El prefijo debe ser texto',
				'prefix.max' => 'El prefijo tiene un maximo de :max caracteres',
				'dependence.required' => 'La dependencia es requerida',
				'dependence.exists' => 'La dependencia no se encuentra regristrada',
			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = new Resolution();
			$data->code = strtoupper($request->code);
			$data->number = $request->number;
			$data->key = $request->key;
			$data->start_date = $request->startDate;
			$data->end_date = $request->endDate;
			$data->start_consecutive = $request->startConsecutive;
			$data->end_consecutive = $request->endConsecutive;
			$data->prefix = $request->prefix;
			$data->dependences_id = $request->dependence;
			$data->active = $request->active;
			$data->users_id = $request->user()->id;
			$data->save();
			DB::commit();
			if ($data) {
				return response()->json(['message' => 'Guardado con exito'], 200);
			}
		} catch (QueryException $e) {
			DB::rollback();
			Log::error($e->getMessage());
			return response()->json(['message' => ['Error del servidor']], 500);
		}
	}
	public function update(Request $request)
	{
		try {
			$rules = [
				'number' => ['required', 'string', 'max:200'],
				'key' => ['required', 'string', 'max:200'],
				'startDate' => ['required', 'date'],
				'endDate' => ['required', 'date'],
				'startConsecutive' => ['required', 'numeric', 'digits_between:1,10'],
				'endConsecutive' => ['required', 'numeric', 'digits_between:1,10'],
				'prefix' => ['required', 'string', 'max:10'],
				'dependence' => ['required', 'exists:App\Models\Dependence,id'],
				'active' => ['required', 'boolean'],
			];

			$validateUnique = Resolution::where('id', $request->id)->first('code');
			if ($validateUnique->code != $request->code) {
				$rules += ['code' => ['required', 'string', 'min:3', 'max:10', 'unique:App\Models\Resolution,code']];
			} else {
				$rules += ['code' => ['required', 'string', 'max:10']];
			}

			$validator = Validator::make($request->all(), $rules, $messages = [
				'code.required' => 'El codigo es requerido',
				'code.string' => 'El codigo debe ser texto',
				'code.max' => 'El codigo tiene un maximo de :max caracteres',
				'code.min' => 'El codigo tiene un minimo de :min caracteres',
				'code.unique' => 'El codigo ya se encuentra registrado',
				'number.required' => 'El numero es requerido',
				'number.string' => 'El numero debe ser texto',
				'number.max' => 'El numero tiene un maximo de :max caracteres',
				'key.required' => 'La llave es requerida',
				'key.string' => 'La llave debe ser texto',
				'key.max' => 'La llave tiene un maximo de :max caracteres',
				'startDate.required' => 'La fecha inicial es requerida',
				'startDate.date' => 'La fecha inicial no es una fecha valida',
				'endDate.required' => 'La fecha final es requerida',
				'endDate.date' => 'La fecha final no es una fecha valida',
				'startConsecutive.required' => 'El consecutivo inicial es requerido',
				'startConsecutive.numeric' => 'El consecutivo inicial debe ser numerico',
				'startConsecutive.digits_between' => 'El consecutivo inicial tiene un maximo de :max y un minimo de :min caracteres',
				'endConsecutive.required' => 'El consecutivo final es requerido',
				'endConsecutive.numeric' => 'El consecutivo final debe ser numerico',
				'endConsecutive.digits_between' => 'El consecutivo final tiene un maximo de :max y un minimo de :min caracteres',
				'prefix.required' => 'El prefijo es requerido',
				'prefix.string' => 'El prefijo debe ser texto',
				'prefix.max' => 'El prefijo tiene un maximo de :max caracteres',
				'dependence.required' => 'La dependencia es requerida',
				'dependence.exists' => 'La dependencia no se encuentra regristrada',
				'active.required' => 'El estado es requerido',
				'active.boolean' => 'El estado no es valido',
			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}

			DB::beginTransaction();
			$data = Resolution::find($request->id);
			$data->code = strtoupper($request->code);
			$data->number = $request->number;
			$data->key = $request->key;
			$data->start_date = $request->startDate;
			$data->end_date = $request->endDate;
			$data->start_consecutive = $request->startConsecutive;
			$data->end_consecutive = $request->endConsecutive;
			$data->prefix = $request->prefix;
			$data->dependences_id = $request->dependence;
			$data->active = $request->active;
			$data->users_id = $request->user()->id;
			$data->save();
			DB::commit();
			if ($data) {
				return response()->json(['message' => 'Actualizado con exito'], 200);
			}
		} catch (QueryException $e) {
			DB::rollback();
			Log::error($e->getMessage());
			return response()->json(['message' => ['Error del servidor']], 500);
		}
	}
	public function delete($id)
	{
		try {
			$resolution = Resolution::where('id', $id)->first();
			if (InvoiceHeader::where('code', $resolution->code)->first()) {
				return response()->json(['message' => 'La Resolucion ' . $resolution->code . ' tiene facturas asociadas'], 500);
			}
			DB::beginTransaction();
			$data = Resolution::find($id);
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
