<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Provider;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class ProviderController extends Controller
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
				$data = Provider::select('providers.*', 'departaments.id as departaments_id')
					->join('cities', 'cities.id', '=', 'providers.cities_id')
					->join('departaments', 'departaments.id', '=', 'cities.departaments_id')
					->where('providers.id', $id)
					->first();
			} else {
				$data = Provider::select('providers.*', 'cities.name as city', 'departaments.name as departament', 'departaments.id as departaments_id', 'type_clients.name as type_client', 'type_regimes.name as type_regime', 'type_documents.short_name as type_document', 'software_data.name as software_data')
					->join('cities', 'cities.id', '=', 'providers.cities_id')
					->join('departaments', 'departaments.id', '=', 'cities.departaments_id')
					->join('type_clients', 'type_clients.id', '=', 'providers.type_clients_id')
					->join('type_regimes', 'type_regimes.id', '=', 'providers.type_regimes_id')
					->join('type_documents', 'type_documents.id', '=', 'providers.type_documents_id')
					->join('software_data', 'software_data.id', '=', 'providers.software_data_id')
					->get();
			}
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen proveedores'], 404);
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
				'officeName' => ['required', 'string', 'max:200'],
				'typeClient' => ['required', 'exists:App\Models\TypeClient,id'],
				'typeRegime' =>  ['required', 'exists:App\Models\TypeRegime,id'],
				'typeDocument' => ['required', 'exists:App\Models\TypeDocument,id'],
				'document' => ['required', 'numeric', 'digits_between:1,100', 'unique:App\Models\Provider,document'],
				'phone' => ['required', 'numeric', 'digits_between:1,20'],
				'email' => ['required', 'email', 'max:100'],
				'address' => ['required', 'string', 'max:200'],
				'city' => ['required', 'exists:App\Models\City,id'],
				'agentName' => ['required', 'string', 'max:200'],
				'agentDocument' => ['required', 'numeric', 'digits_between:1,100'],
				'emailAutoship' => ['required', 'email', 'max:100'],
				'dianTest' => ['required'],
				'software' => ['required', 'exists:App\Models\SoftwareData,id'],
			];

			$validator = Validator::make($request->all(), $rules, $messages = [
				'officeName.required' => 'El nombre de la oficina es requerido',
				'officeName.string' => 'El nombre de la oficina debe ser texto',
				'officeName.max' => 'El nombre de la oficina tiene un maximo de :max caracteres',
				'typeClient.required' => 'El tipo de cliente es requerido',
				'typeClient.exists' => 'El tipo de cliente no se encuentra registrado',
				'typeRegime.required' => 'El tipo de regimen es requerido',
				'typeRegime.exists' => 'El tipo de regimen no se encuentra registrado',
				'typeDocument.required' => 'El tipo de documento es requerido',
				'typeDocument.exists' => 'El tipo de documento no se encuentra registrado',
				'document.required' => 'El documento es requerido',
				'document.numeric' => 'El documento debe ser numerico',
				'document.digits_between' => 'El documento tiene un maximo de :max y un minimo de :min caracteres',
				'document.unique' => 'El documento ya se encuentra registrado',
				'phone.required' => 'El telefono es requerido',
				'phone.numeric' => 'El telefono debe ser numerico',
				'phone.digits_between' => 'El documento tiene un maximo de :max y un minimo de :min caracteres',
				'email.required' => 'El correo es requerido',
				'email.email' => 'El correo es invalido',
				'email.max' => 'El correo tiene un maximo de :max caracteres',
				'address.required' => 'La direccion es requerida',
				'address.string' => 'La direccion debe ser texto',
				'address.max' => 'El correo tiene un maximo de :max caracteres',
				'city.required' => 'La ciudad es requerida',
				'city.exists' => 'La ciudad no se encuentra registrada',
				'agentName.required' => 'El nombre del representante es requerido',
				'agentName.string' => 'El nombre del representante debe ser texto',
				'agentName.max' => 'El nombre del representante tiene un maximo de :max caracteres',
				'agentDocument.required' => 'El documento del representante es requerido',
				'agentDocument.numeric' => 'El documento del representante debe ser numerico',
				'agentDocument.digits_between' => 'El documento del representante tiene un maximo de :max y un minimo de :min caracteres',
				'emailAutoship.required' => 'El correo de autoenvio es requerido',
				'emailAutoship.email' => 'El correo de autoenvio es invalido',
				'emailAutoship.max' => 'El correo de autoenvio tiene un maximo de :max caracteres',
				'dianTest.required' => 'El entorno de prueba es requerido',
				'software.required' => 'Los datos del software son requeridos',
				'software.exists' => 'Los datos del software no se encuentran registrados',

			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = new Provider();
			$data->office_name = $request->officeName;
			$data->type_clients_id = $request->typeClient;
			$data->type_regimes_id = $request->typeRegime;
			$data->type_documents_id = $request->typeDocument;
			$data->document = $request->document;
			$data->phone = $request->phone;
			$data->email = $request->email;
			$data->address = $request->address;
			$data->cities_id = $request->city;
			$data->agent_name = $request->agentName;
			$data->agent_document = $request->agentDocument;
			$data->email_autoship = $request->emailAutoship;
			$data->dian_test = $request->dianTest;
			$data->software_data_id = $request->software;
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
				'officeName' => ['required', 'string', 'max:200'],
				'typeClient' => ['required', 'exists:App\Models\TypeClient,id'],
				'typeRegime' =>  ['required', 'exists:App\Models\TypeRegime,id'],
				'typeDocument' => ['required', 'exists:App\Models\TypeDocument,id'],
				'phone' => ['required', 'numeric', 'digits_between:5,20'],
				'email' => ['required', 'email', 'max:100'],
				'address' => ['required', 'string', 'max:200'],
				'city' => ['required', 'exists:App\Models\City,id'],
				'agentName' => ['required', 'string', 'max:200'],
				'agentDocument' => ['required', 'numeric', 'digits_between:5,100'],
				'emailAutoship' => ['required', 'email', 'max:100'],
				'dianTest' => ['required'],
				'software' => ['required', 'exists:App\Models\SoftwareData,id'],
			];

			$validateDocument = Provider::where('id', $request->id)->first('document');
			if ($validateDocument->document != $request->document) {
				$rules += ['document' => ['required', 'numeric', 'digits_between:5,100', 'unique:App\Models\Provider,document']];
			} else {
				$rules += ['document' => ['required', 'numeric', 'digits_between:5,100']];
			}

			$validator = Validator::make($request->all(), $rules, $messages = [
				'officeName.required' => 'El nombre de la oficina es requerido',
				'officeName.string' => 'El nombre de la oficina debe ser texto',
				'officeName.max' => 'El nombre de la oficina tiene un maximo de :max caracteres',
				'typeClient.required' => 'El tipo de cliente es requerido',
				'typeClient.exists' => 'El tipo de cliente no se encuentra registrado',
				'typeRegime.required' => 'El tipo de regimen es requerido',
				'typeRegime.exists' => 'El tipo de regimen no se encuentra registrado',
				'typeDocument.required' => 'El tipo de documento es requerido',
				'typeDocument.exists' => 'El tipo de documento no se encuentra registrado',
				'document.required' => 'El documento es requerido',
				'document.numeric' => 'El documento debe ser numerico',
				'document.digits_between' => 'El documento tiene un maximo de :max y un minimo de :min caracteres',
				'document.unique' => 'El documento ya se encuentra registrado',
				'phone.required' => 'El telefono es requerido',
				'phone.numeric' => 'El telefono debe ser numerico',
				'phone.digits_between' => 'El documento tiene un maximo de :max y un minimo de :min caracteres',
				'email.required' => 'El correo es requerido',
				'email.email' => 'El correo es invalido',
				'email.max' => 'El correo tiene un maximo de :max caracteres',
				'address.required' => 'La direccion es requerida',
				'address.string' => 'La direccion debe ser texto',
				'address.max' => 'El correo tiene un maximo de :max caracteres',
				'city.required' => 'La ciudad es requerida',
				'city.exists' => 'La ciudad no se encuentra registrada',
				'agentName.required' => 'El nombre del representante es requerido',
				'agentName.string' => 'El nombre del representante debe ser texto',
				'agentName.max' => 'El nombre del representante tiene un maximo de :max caracteres',
				'agentDocument.required' => 'El documento del representante es requerido',
				'agentDocument.numeric' => 'El documento del representante debe ser numerico',
				'agentDocument.digits_between' => 'El documento del representante tiene un maximo de :max y un minimo de :min caracteres',
				'emailAutoship.required' => 'El correo de autoenvio es requerido',
				'emailAutoship.email' => 'El correo de autoenvio es invalido',
				'emailAutoship.max' => 'El correo de autoenvio tiene un maximo de :max caracteres',
				'dianTest.required' => 'El entorno de prueba es requerido',
				'software.required' => 'Los datos del software son requeridos',
				'software.exists' => 'Los datos del software no se encuentran registrados',

			]);
			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = Provider::find($request->id);
			$data->office_name = $request->officeName;
			$data->type_clients_id = $request->typeClient;
			$data->type_regimes_id = $request->typeRegime;
			$data->type_documents_id = $request->typeDocument;
			$data->document = $request->document;
			$data->phone = $request->phone;
			$data->email = $request->email;
			$data->address = $request->address;
			$data->cities_id = $request->city;
			$data->agent_name = $request->agentName;
			$data->agent_document = $request->agentDocument;
			$data->email_autoship = $request->emailAutoship;
			$data->dian_test = $request->dianTest;
			$data->software_data_id = $request->software;
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
			if ($institution = Institution::where('providers_id', $id)->first()) {
				return response()->json(['message' => 'La Institucion ' . $institution->name . ' depende de este dato'], 500);
			}
			DB::beginTransaction();
			$data = Provider::find($id);
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
