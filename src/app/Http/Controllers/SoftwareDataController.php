<?php

namespace App\Http\Controllers;

use App\Http\Requests\SoftwareDataRequest;
use App\Models\Provider;
use App\Models\SoftwareData;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class SoftwareDataController extends Controller
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
				$data = SoftwareData::where('id', $id)->first();
			} else {
				$data = SoftwareData::all();
			}
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen datos del software'], 404);
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
				'name' => ['required', 'string', 'max:200'],
				'pin' => ['required', 'string', 'max:200'],
				'testId' => ['required', 'string', 'max:200'],
				'identification' => ['required', 'string', 'max:200', 'unique:App\Models\SoftwareData,identification']
			];

			$validator = Validator::make($request->all(), $rules, $messages = [
				'name.required' => 'El nombre es requerido',
				'name.string' => 'El nombre debe ser texto',
				'name.max' => 'El nombre tiene un maximo de :max caracteres',
				'pin.required' => 'El pin es requerido',
				'pin.string' => 'El pin debe ser texto',
				'pin.max' => 'El pin tiene un maximo de :max caracteres',
				'identification.required' => 'La identificacion es requerida',
				'identification.string' => 'La identificacion debe ser texto',
				'identification.max' => 'La identificacion tiene un maximo de :max caracteres',
				'identification.unique' => 'La identifiacion ya se encuentra registrada',
				'testId.required' => 'El test ID es requerido',
				'testId.string' => 'El test ID debe ser texto',
				'testId.max' => 'El test ID tiene un maximo de :max caracteres',
			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = new SoftwareData();
			$data->name = $request->name;
			$data->pin = $request->pin;
			$data->identification = $request->identification;
			$data->test_id = $request->testId;
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
				'name' => ['required', 'string', 'max:200'],
				'pin' => ['required', 'string', 'max:200'],
				'testId' => ['required', 'string', 'max:200'],
			];
			$validationIdentification = SoftwareData::where('id', $request->id)->first('identification');
			if ($validationIdentification->identification != $request->identification) {
				$rules += ['identification' => ['required', 'string', 'max:200', 'unique:App\Models\SoftwareData,identification']];
			} else {
				$rules += ['identification' => ['required', 'string', 'max:200']];
			}

			$validator = Validator::make($request->all(), $rules, $messages = [
				'name.required' => 'El nombre es requerido',
				'name.string' => 'El nombre debe ser texto',
				'name.max' => 'El nombre tiene un maximo de :max caracteres',
				'pin.required' => 'El pin es requerido',
				'pin.string' => 'El pin debe ser texto',
				'pin.max' => 'El pin tiene un maximo de :max caracteres',
				'identification.required' => 'La identificacion es requerida',
				'identification.string' => 'La identificacion debe ser texto',
				'identification.max' => 'La identificacion tiene un maximo de :max caracteres',
				'identification.unique' => 'La identifiacion ya se encuentra registrada',
				'testId.required' => 'El test ID es requerido',
				'testId.string' => 'El test ID debe ser texto',
				'testId.max' => 'El test ID tiene un maximo de :max caracteres',
			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}

			DB::beginTransaction();
			$data = SoftwareData::find($request->id);
			$data->name = $request->name;
			$data->pin = $request->pin;
			$data->identification = $request->identification;
			$data->test_id = $request->testId;
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
			if ($provider = Provider::where('software_data_id', $id)->first()) {
				return response()->json(['message' => 'El Proveedor ' . $provider->office_name . ' depende de este dato'], 500);
			}
			DB::beginTransaction();
			$data = SoftwareData::find($id);
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
