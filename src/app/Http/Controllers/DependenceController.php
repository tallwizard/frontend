<?php

namespace App\Http\Controllers;

use App\Models\Dependence;
use App\Models\Resolution;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class DependenceController extends Controller
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
				$data = Dependence::where('id', $id)->first();
			} else {
				$data = Dependence::select('dependences.*', 'institutions.name as institution')
					->join('institutions', 'institutions.id', '=', 'dependences.institutions_id')
					->orderBy('dependences.name')
					->get();
			}
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen dependencias'], 404);
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
				'code' => ['required', 'string', 'max:20'],
				'name' => ['required', 'string', 'max:200'],
				'institution' =>  ['required', 'exists:App\Models\Institution,id'],
			];

			$validator = Validator::make($request->all(), $rules, $messages = [
				'code.required' => 'El codigo es requerido',
				'code.string' => 'El codigo debe ser texto',
				'code.max' => 'El codigo tiene un maximo de :max caracteres',
				'name.required' => 'El nombre es requerido',
				'name.string' => 'El nombre debe ser texto',
				'name.max' => 'El nombre tiene un maximo de :max caracteres',
				'institution.required' => 'La institucion es requerida',
				'institution.exists' => 'El institucion no se encuentra registrada',
			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = new Dependence();
			$data->code = $request->code;
			$data->name = $request->name;
			$data->institutions_id = $request->institution;
			$data->users_id = $request->user()->id;
			$data->save();
			DB::commit();
			if ($data) {
				return response()->json(['message' => 'Guardado con exito'], 200);
			}
		} catch (QueryException $e) {
			DB::rollback();
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}
	public function update(Request $request)
	{
		try {
			$rules = [
				'code' => ['required', 'string', 'max:20'],
				'name' => ['required', 'string', 'max:200'],
				'institution' =>  ['required', 'exists:App\Models\Institution,id'],
			];

			$validator = Validator::make($request->all(), $rules, $messages = [
				'code.required' => 'El codigo es requerido',
				'code.string' => 'El codigo debe ser texto',
				'code.max' => 'El codigo tiene un maximo de :max caracteres',
				'name.required' => 'El nombre es requerido',
				'name.string' => 'El nombre debe ser texto',
				'name.max' => 'El nombre tiene un maximo de :max caracteres',
				'institution.required' => 'La institucion es requerida',
				'institution.exists' => 'El institucion no se encuentra registrada',
			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = Dependence::find($request->id);
			$data->code = $request->code;
			$data->name = $request->name;
			$data->institutions_id = $request->institution;
			$data->users_id = $request->user()->id;
			$data->save();
			DB::commit();
			if ($data) {
				return response()->json(['message' => 'Actualizado con exito'], 200);
			}
		} catch (QueryException $e) {
			DB::rollback();
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}
	public function delete($id)
	{
		try {
			if ($resolution = Resolution::where('dependences_id', $id)->first()) {
				return response()->json(['message' => 'La Resolucion ' . $resolution->code . ' depende de este dato'], 500);
			}
			DB::beginTransaction();
			$data = Dependence::find($id);
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
