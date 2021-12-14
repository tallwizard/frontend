<?php

namespace App\Http\Controllers;

use App\Models\Dependence;
use App\Models\Institution;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class InstitutionController extends Controller
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
				$data = Institution::where('id', $id)->first();
			} else {
				$data = Institution::select('institutions.*', 'providers.office_name as provider')
					->join('providers', 'providers.id', '=', 'institutions.providers_id')
					->orderBy('institutions.name')
					->get();
			}
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen instituciones'], 404);
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
				'provider' =>  ['required', 'exists:App\Models\Provider,id'],
			];

			$validator = Validator::make($request->all(), $rules, $messages = [
				'code.required' => 'El codigo es requerido',
				'code.string' => 'El codigo debe ser texto',
				'code.max' => 'El codigo tiene un maximo de :max caracteres',
				'name.required' => 'El nombre es requerido',
				'name.string' => 'El nombre debe ser texto',
				'name.max' => 'El nombre tiene un maximo de :max caracteres',
				'provider.required' => 'El proveedor es requerido',
				'provider.exists' => 'El proveedor no se encuentra registrado',
			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = new Institution();
			$data->code = $request->code;
			$data->name = $request->name;
			$data->providers_id = $request->provider;
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
				'code' => ['required', 'string', 'max:20'],
				'name' => ['required', 'string', 'max:200'],
				'provider' =>  ['required', 'exists:App\Models\Provider,id'],
			];

			$validator = Validator::make($request->all(), $rules, $messages = [
				'code.required' => 'El codigo es requerido',
				'code.string' => 'El codigo debe ser texto',
				'code.max' => 'El codigo tiene un maximo de :max caracteres',
				'name.required' => 'El nombre es requerido',
				'name.string' => 'El nombre debe ser texto',
				'name.max' => 'El nombre tiene un maximo de :max caracteres',
				'provider.required' => 'El proveedor es requerido',
				'provider.exists' => 'El proveedor no se encuentra registrado',
			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = Institution::find($request->id);
			$data->code = $request->code;
			$data->name = $request->name;
			$data->providers_id = $request->provider;
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
			if ($dependence = Dependence::where('institutions_id', $id)->first()) {
				return response()->json(['message' => 'La Dependencia ' . $dependence->name . ' depende de este dato'], 500);
			}
			DB::beginTransaction();
			$data = Institution::find($id);
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
