<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Client;
use App\Models\Departament;
use App\Models\TypeClient;
use App\Models\TypeDocument;
use App\Models\TypeRegime;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;

class ClientController extends Controller
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

	public function autocompleteClient($request = null)
	{
		if ($request) {
			try {
				$data = DB::table('clients')->select('document as value', DB::raw("CONCAT(name,' ',last_name,' - ',document) as label"))->orderBy('name');
				if (is_numeric($request)) {
					$data->where('clients.document', 'like', '%' . $request . '%');
				} else {
					$data->where(DB::raw("CONCAT(clients.name,' ',clients.last_name)"), 'like', '%' . $request . '%');
				}
				$data = $data->limit(10)->get();
				if ($data->count()) {
					return response()->json($data, 200);
				} else {
					return response()->json(['message' => 'No existen datos'], 404);
				}
			} catch (QueryException $e) {
				Log::info($e->getMessage());
				return response()->json(['message' => 'Error del servidor']);
			}
		}
	}

	public function consultIndex($id = null)
	{
		try {
			if ($id) {
				$response = Client::where('id', $id)->first();
			} else {
				$data = Client::select('clients.*', 'type_clients.name as type_client', 'type_documents.short_name as type_document', 'cities.name as city', 'departaments.name as departament', 'cities.zip_code as zip_code')
					->join('type_clients', 'type_clients.id', '=', 'clients.type_clients_id')
					->join('type_documents', 'type_documents.id', '=', 'clients.type_documents_id')
					->join('cities', 'cities.id', '=', 'clients.cities_id')
					->join('departaments', 'departaments.id', '=', 'cities.departaments_id')
					->whereDate('clients.updated_at',  Carbon::today())->get();
				foreach ($data as $key => $value) {
					$response[] = [
						'id' => $value->id,
						'name' => $value->name,
						'lastName' => $value->last_name,
						'typeClient' => $value->type_client,
						'typeDocument' => $value->type_document,
						'document' => $value->document,
						'phone' => $value->phone,
						'email' => $value->email,
						'departament' => $value->departament,
						'city' => $value->city,
						'zipCode' => $value->zip_code,
						'address' => $value->address,
					];
				}
			}
			if (!empty($response)) {
				return response()->json(['data' => $response], 200);
			} else {
				return response()->json(['message' => 'No existen terceros'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function update(Request $request)
	{
		try {
			$rules = [
				'name' => ['required', 'string', 'max:200'],
				'lastName' => ['nullable', 'string', 'max:200'],
				'typeClient' => ['required', 'exists:App\Models\TypeClient,id'],
				'typeDocument' => ['required', 'exists:App\Models\TypeDocument,id'],
				'phone' => ['required', 'numeric', 'digits_between:5,20'],
				'email' => ['required', 'email', 'max:100'],
				'address' => ['required', 'string', 'max:200'],
				'city' => ['required', 'exists:App\Models\City,id'],
			];
			$validateDocument = Client::where('id', $request->id)->first('document');
			if ($validateDocument->document != $request->document) {
				$rules += ['document' => ['required', 'numeric', 'digits_between:5,100', 'unique:App\Models\Client,document']];
			} else {
				$rules += ['document' => ['required', 'numeric', 'digits_between:5,100']];
			}

			$validator = Validator::make($request->all(), $rules, $messages = [
				'name.required' => 'El nombre es requerido',
				'name.string' => 'El nombre debe ser texto',
				'name.max' => 'El nombre tiene un maximo de :max caracteres',
				'lastName.string' => 'El apellido debe ser texto',
				'lastName.max' => 'El apellido tiene un maximo de :max caracteres',
				'typeClient.required' => 'El tipo de cliente es requerido',
				'typeClient.exists' => 'El tipo de cliente no existe',
				'typeDocument.required' => 'El tipo de documento es requerido',
				'typeDocument.exists' => 'El tipo de documento no existe',
				'document.required' => 'El documento es requerido',
				'document.numeric' => 'El documento debe ser numerico',
				'document.digits_between' => 'El documento tiene un maximo de :max caracteres',
				'document.unique' => 'El documento ya se encuentra registrado',
				'phone.required' => 'El telefono es requerido',
				'phone.numeric' => 'El telefono debe ser numerico',
				'phone.digits_between' => 'El documento tiene un maximo de :max caracteres',
				'email.required' => 'El correo es requerido',
				'email.email' => 'El correo es invalido',
				'email.max' => 'El correo tiene un maximo de :max caracteres',
				'address.required' => 'La direccion es requerida',
				'address.string' => 'La direccion debe ser texto',
				'address.max' => 'El correo tiene un maximo de :max caracteres',
				'city.required' => 'La ciudad es requerida',
				'city.exists' => 'La ciudad no existe',
			]);

			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = Client::find($request->id);
			$data->name = $request->name;
			$data->last_name = $request->lastName;
			$data->type_clients_id = $request->typeClient;
			$data->type_documents_id = $request->typeDocument;
			$data->document = $request->document;
			$data->phone = $request->phone;
			$data->email = $request->email;
			$data->cities_id = $request->city;
			$data->address = $request->address;
			$data->users_id = Auth::user()->id;
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
			DB::beginTransaction();
			$data = Client::find($id);
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

	public function consultClient(Request $request)
	{
		try {
			$data = DB::table('clients')->select('clients.*', 'type_clients.name as type_client', 'type_documents.short_name as type_document', 'cities.name as city', 'departaments.name as departament', 'cities.zip_code as zip_code')
				->join('type_clients', 'type_clients.id', '=', 'clients.type_clients_id')
				->join('type_documents', 'type_documents.id', '=', 'clients.type_documents_id')
				->join('cities', 'cities.id', '=', 'clients.cities_id')
				->join('departaments', 'departaments.id', '=', 'cities.departaments_id');
			if ($request->dateStart && $request->dateEnd) {
				$data->whereBetween('clients.updated_at', [$request->dateStart . ' 00:00:00', $request->dateEnd . ' 23:59:59']);
			} else if ($request->dateStart) {
				$data->whereDate('clients.updated_at',  date($request->dateStart));
			} else if ($request->dateEnd) {
				$data->whereDate('clients.updated_at',  date($request->dateEnd));
			}
			if ($request->name) {
				$data->where('clients.name', 'like', '%' . $request->name . '%');
			}
			if ($request->document) {
				$data->where('clients.document', $request->document);
			}
			if ($request->email) {
				$data->where('clients.email', 'like', '%' . $request->email . '%');
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

	public function validateData(Request $request)
	{
		$rules = [
			'name' => ['required', 'string', 'max:200'],
			'lastName' => ['string', 'max:200'],
			'typeClient' => ['required', 'exists:App\Models\TypeClient,id'],
			'typeDocument' => ['required', 'exists:App\Models\TypeDocument,id'],
			'document' => ['required', 'numeric', 'digits_between:5,100', 'unique:App\Models\Client,document'],
			'phone' => ['required', 'numeric', 'digits_between:5,20'],
			'email' => ['required', 'email', 'max:100'],
			'address' => ['required', 'string', 'max:200'],
			'city' => ['required', 'exists:App\Models\City,zip_code'],
		];

		foreach ($request->all() as $key => $value) {

			$validator = Validator::make($value, $rules, $messages = [
				'name.required' => 'El nombre es requerido en la fila: ' . ($key + 1),
				'name.string' => 'El nombre debe ser texto en la fila: ' . ($key + 1),
				'name.max' => 'El nombre tiene un maximo de :max caracteres en la fila: ' . ($key + 1),
				'lastName.string' => 'El apellido debe ser texto en la fila: ' . ($key + 1),
				'lastName.max' => 'El apellido tiene un maximo de :max caracteres en la fila: ' . ($key + 1),
				'typeClient.required' => 'El tipo de cliente es requerido en la fila: ' . ($key + 1),
				'typeClient.exists' => 'El tipo de cliente no existe en la fila: ' . ($key + 1),
				'typeDocument.required' => 'El tipo de documento es requerido en la fila: ' . ($key + 1),
				'typeDocument.exists' => 'El tipo de documento no existe en la fila: ' . ($key + 1),
				'document.required' => 'El documento es requerido en la fila: ' . ($key + 1),
				'document.numeric' => 'El documento debe ser numerico en la fila: ' . ($key + 1),
				'document.digits_between' => 'El documento tiene un maximo de :max caracteres en la fila: ' . ($key + 1),
				'document.unique' => 'El documento ya se encuentra registrado en la fila: ' . ($key + 1),
				'phone.required' => 'El telefono es requerido en la fila: ' . ($key + 1),
				'phone.numeric' => 'El telefono debe ser numerico en la fila: ' . ($key + 1),
				'phone.digits_between' => 'El documento tiene un maximo de :max caracteres en la fila: ' . ($key + 1),
				'email.required' => 'El correo es requerido en la fila: ' . ($key + 1),
				'email.email' => 'El correo es invalido en la fila: ' . ($key + 1),
				'email.max' => 'El correo tiene un maximo de :max caracteres en la fila: ' . ($key + 1),
				'address.required' => 'La direccion es requerida en la fila: ' . ($key + 1),
				'address.string' => 'La direccion debe ser texto en la fila: ' . ($key + 1),
				'address.max' => 'El correo tiene un maximo de :max caracteres en la fila: ' . ($key + 1),
				'city.required' => 'La ciudad es requerida en la fila: ' . ($key + 1),
				'city.exists' => 'La ciudad no existe en la fila: ' . ($key + 1),
			]);
			$errors[] = $validator->errors()->all();
		}
		if ($validator->fails()) {
			return response()->json(['message' => $errors], 400);
		} else {
			return response()->json(['message' => 'Terceros sin errores'], 200);
		}
	}

	public function importData(Request $request)
	{
		$data = $this->unique_multidim_array($request->all(), 'document');
		try {
			DB::beginTransaction();
			foreach ($data as $key => $value) {
				$city = City::where('zip_code', $value['city'])->first('id');
				$client = new Client();
				$client->name = $value['name'];
				$client->last_name = $value['lastName'];
				$client->type_clients_id = $value['typeClient'];
				$client->type_documents_id = $value['typeDocument'];
				$client->document = $value['document'];
				$client->phone = $value['phone'];
				$client->email = $value['email'];
				$client->cities_id = $city->id;
				$client->address = $value['address'];
				$client->users_id = Auth::user()->id;
				$client->save();
			}
		} catch (QueryException $e) {
			DB::rollback();
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
		DB::commit();
		return response()->json(['message' => 'Guardado con exito'], 200);
	}

	public function create(Request $request)
	{
		try {
			$rules = [
				'name' => ['required', 'string', 'max:200'],
				'lastName' => ['nullable', 'string', 'max:200'],
				'typeClient' => ['required', 'exists:App\Models\TypeClient,id'],
				'typeDocument' => ['required', 'exists:App\Models\TypeDocument,id'],
				'document' => ['required', 'numeric', 'digits_between:5,100', 'unique:App\Models\Client,document'],
				'phone' => ['required', 'numeric', 'digits_between:5,20'],
				'email' => ['required', 'email', 'max:100'],
				'address' => ['required', 'string', 'max:200'],
				'city' => ['required', 'exists:App\Models\City,id'],
			];

			$validator = Validator::make($request->all(), $rules, $messages = [
				'name.required' => 'El nombre es requerido',
				'name.string' => 'El nombre debe ser texto',
				'name.max' => 'El nombre tiene un maximo de :max caracteres',
				'lastName.string' => 'El apellido debe ser texto',
				'lastName.max' => 'El apellido tiene un maximo de :max caracteres',
				'typeClient.required' => 'El tipo de cliente es requerido',
				'typeClient.exists' => 'El tipo de cliente no existe',
				'typeDocument.required' => 'El tipo de documento es requerido',
				'typeDocument.exists' => 'El tipo de documento no existe',
				'document.required' => 'El documento es requerido',
				'document.numeric' => 'El documento debe ser numerico',
				'document.digits_between' => 'El documento tiene un maximo de :max caracteres',
				'document.unique' => 'El documento ya se encuentra registrado',
				'phone.required' => 'El telefono es requerido',
				'phone.numeric' => 'El telefono debe ser numerico',
				'phone.digits_between' => 'El documento tiene un maximo de :max caracteres',
				'email.required' => 'El correo es requerido',
				'email.email' => 'El correo es invalido',
				'email.max' => 'El correo tiene un maximo de :max caracteres',
				'address.required' => 'La direccion es requerida',
				'address.string' => 'La direccion debe ser texto',
				'address.max' => 'El correo tiene un maximo de :max caracteres',
				'city.required' => 'La ciudad es requerida',
				'city.exists' => 'La ciudad no existe',
			]);

			$errors = $validator->errors();
			if ($errors->all()) {
				return response()->json(['message' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$data = new Client();
			$data->name = $request->name;
			$data->last_name = $request->lastName;
			$data->type_clients_id = $request->typeClient;
			$data->type_documents_id = $request->typeDocument;
			$data->document = $request->document;
			$data->phone = $request->phone;
			$data->email = $request->email;
			$data->cities_id = $request->city;
			$data->address = $request->address;
			$data->users_id = Auth::user()->id;
			$data->save();
			DB::commit();
		} catch (QueryException $e) {
			DB::rollback();
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
		return response()->json(['message' => 'Guardado con exito'], 200);
	}

	public function getTypeClient($id = null)
	{
		try {
			if ($id) {
				$data = TypeClient::where('id', $id)->first();
			} else {
				$data = TypeClient::orderBy('name')->get();
			}
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen tipos de terceros'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function getTypeRegime($id = null)
	{
		try {
			if ($id) {
				$data = TypeRegime::where('id', $id)->first();
			} else {
				$data = TypeRegime::orderBy('name')->get();
			}
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen tipos de regimen'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function getTypeDocument($id = null)
	{
		try {
			if ($id) {
				$data = TypeDocument::where('id', $id)->first();
			} else {
				$data = TypeDocument::orderBy('name')->get();
			}
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen tipos de documento'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function getCity($id = null)
	{
		try {
			if ($id) {
				$data = City::where('id', $id)->first();
			} else {
				$data = City::orderBy('name')->get();
			}
			if ($data->count()) {
				return response()->json(['data' => $data], 200);
			} else {
				return response()->json(['message' => 'No existen ciudades'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function unique_multidim_array($array, $key)
	{
		$temp_array = array();
		$i = 0;
		$key_array = array();

		foreach ($array as $val) {
			if (!in_array($val[$key], $key_array)) {
				$key_array[$i] = $val[$key];
				$temp_array[$i] = $val;
			}
			$i++;
		}
		return $temp_array;
	}
}
