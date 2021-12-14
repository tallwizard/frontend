<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;
use Laravel\Passport\Exceptions\OAuthServerException;

class UserController extends Controller
{
	public function listUsers($id = null)
	{
		try {
			if ($id) {
				if (Auth::user()->roles_id === 1) {
					$data = User::where('id', $id)->first();
				} else {
					$data = User::where('id', '=', Auth::user()->id)->first();
				}
			} else {
				if (Auth::user()->roles_id === 1) {
					$data = User::select('users.id', 'users.name', 'roles.name as role', 'users.email', 'users.active')
						->join('roles', 'users.roles_id', '=', 'roles.id')
						->where('users.id', '!=', Auth::user()->id)->get();
				} else {
					return response()->json(['message' => 'No posee privilegios'], 500);
				}
			}
			if ($data->count()) {
				return response()->json($data, 200);
			} else {
				return response()->json(['message' => 'No existen datos'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function update(Request $request)
	{
		$email = false;
		$password = false;
		$user = User::where('id', $request->id)->first('email');
		$rules = [
			'name' => ['required', 'string', 'max:200'],
		];


		if ($request->email != $user->email) {
			$email = true;
			$rules += ['email' => ['required', 'email', 'max:100', 'unique:users,email']];
		}

		if ($request->password) {
			$password = true;
			$rules += ['password' => ['string', 'max:20', 'min:5']];
		}
		if ($request->role) {
			$role = true;
			$rules += ['role' => ['required', 'exists:roles,id']];
		}

		try {
			$validatedData = Validator::make($request->all(), $rules, [
				'name.required' => 'El nombre es requerido',
				'name.string' => 'El nombre debe ser texto',
				'name.max' => 'El nombre debe tener un maximo de :max caracteres',
				'email.required' => 'El correo es requerido',
				'email.email' => 'El correo debe ser una direccion de correo electronica valida',
				'email.unique' => 'El correo ya se encuentra registrado',
				'email.max' => 'El correo debe tener un maximo de :max caracteres',
				'password.required' => 'La contraseña es requerida',
				'password.max' => 'La contraseña debe tener un maximo de :max caracteres',
				'password.min' => 'La contraseña debe tener un minimo de :min caracteres',
				'password.string' => 'La contraseña debe ser texto',
				'role.required' => 'El rol es requerido',
				'role.exists' => 'El rol no se encuentra registrado',
			]);

			$errors = $validatedData->errors();
			if ($errors->all()) {
				return response()->json(['errors' => $errors->all()], 400);
			}
			DB::beginTransaction();
			$user = User::find($request->id);
			$user->name = $request->name;
			if ($password) {
				$user->password = bcrypt($request->password);
			}
			if ($email) {
				$user->email = $request->email;
			}
			if ($role) {
				$user->roles_id = $request->role;
			}
			$user->active = $request->active;
			$user->save();
			DB::commit();
			return response()->json(['message' => 'Guardado con exito'], 200);
		} catch (OAuthServerException $e) {
			Log::error($e);
			return response()->json(['message' => 'Error al validar los datos'], 500);
		}
	}

	public function getRoles()
	{
		try {
			$data = Role::where('id', '!=', 1)->get();
			if ($data->count()) {
				return response()->json($data, 200);
			} else {
				return response()->json(['message' => 'No existen datos'], 404);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error al validar los datos'], 500);
		}
	}

	public function create(Request $request)
	{

		if (Auth::user()->roles_id === 1) {
			try {

				$rules = [
					'name' => ['required', 'string', 'max:200'],
					'email' => ['required', 'email', 'unique:users,email', 'max:100'],
					'password' => ['required', 'string', 'min:5', 'max:20'],
					'role' => ['required', 'exists:roles,id'],
				];

				$validatedData = Validator::make($request->all(), $rules, [
					'name.required' => 'El nombre es requerido',
					'name.string' => 'El nombre debe ser texto',
					'name.max' => 'El nombre debe tener un maximo de :max caracteres',
					'email.required' => 'El correo es requerido',
					'email.email' => 'El correo debe ser una direccion de correo electronica valida',
					'email.unique' => 'El correo ya se encuentra registrado',
					'email.max' => 'El correo debe tener un maximo de :max caracteres',
					'password.required' => 'La contraseña es requerida',
					'password.max' => 'La contraseña debe tener un maximo de :max caracteres',
					'password.min' => 'La contraseña debe tener un minimo de :min caracteres',
					'password.string' => 'La contraseña debe ser texto',
					'role.required' => 'El rol es requerido',
					'role.exists' => 'El rol no se encuentra registrado',
				]);

				$errors = $validatedData->errors();
				if ($errors->all()) {
					return response()->json(['errors' => $errors->all()], 400);
				}

				DB::beginTransaction();
				$user = new User();
				$user->name = $request->name;
				$user->email = $request->email;
				$user->password = bcrypt($request->password);
				$user->roles_id = $request->role;
				$user->save();
				DB::commit();
				if ($user) {
					return response()->json(['message' => 'Guardado con exito'], 200);
				} else {
					return response()->json(['message' => 'Error al guardar los datos'], 500);
				}
			} catch (QueryException $e) {
				Log::error($e->getMessage());
				return response()->json(['message' => 'Error al guardar los datos'], 500);
			}
		} else {
			return response()->json(['message' => 'No posee privilegios'], 500);
		}
	}

	public function login(Request $request)
	{
		try {
			$validatedData = Validator::make($request->all(), [
				'email' => 'email|required|exists:users,email',
				'password' => 'required'
			], [
				'email.email' => 'El correo debe ser una direccion de correo electronica valida',
				'email.exists' => 'El correo no se encuentra registrado',
				'email.required' => 'El correo es requerido',
				'password.required' => 'La contraseña es requerida',
			]);

			$errors = $validatedData->errors();
			if ($errors->all()) {
				return response()->json(['errors' => $errors->all()], 400);
			}

			if (User::where([['email', '=', $request->email], ['active', '!=', true]])->first()) {
				return response()->json(['message' => 'El usuario no se encuentra activo'], 400);
			}

			if (!Auth::attempt($request->all())) {
				return response()->json(['message' => 'Credenciales invalidas'], 401);
			}

			$accessToken = Auth::user()->createToken('authToken')->accessToken;
			$data = [
				'id' => Auth::user()->id,
				'email' => Auth::user()->email,
				'name' => Auth::user()->name,
				'role' => (Role::where('id', Auth::user()->roles_id)->first('name'))->name,
				'roles_id' => Auth::user()->roles_id,
				'active' => Auth::user()->active,
			];
			return response()->json(['user' => $data, 'access_token' => $accessToken], 200);
		} catch (OAuthServerException $e) {
			Log::error($e);
			return response()->json(['message' => 'Error al validar los datos'], 500);
		}
	}

	public function logout(Request $request)
	{
		try {
			$token = $request->user()->token();
			$token->revoke();
			return response()->json(['message' => 'Sesion cerrada con exito'], 200);
		} catch (\Throwable $th) {
			Log::error($th);
			return response()->json(['message' => 'Error del servidor'], 500);
		}
	}

	public function validateToken(Request $request)
	{
		try {
			$tokenExpireDate = new DateTime($request->user()->token()->expires_at);
			$currentDate = new DateTime();
			if ($tokenExpireDate >= $currentDate) {
				return response()->json(['message' => 'El token es valido'], 200);
			} else {
				return response()->json(['message' => 'El token es invalido'], 401);
			}
		} catch (OAuthServerException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Ocurrio un error al autenticar'], 500);
		}
	}

	public function delete($id)
	{
		try {
			DB::beginTransaction();
			$user = User::find($id);
			$user->active = false;
			$user->save();
			DB::commit();
			return response()->json(['message' => 'Usuario inactivado con exito'], 200);
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json(['message' => 'Error al validar los datos'], 500);
		}
	}
}
