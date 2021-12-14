<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class InvoiceRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'prefix' => ['required', 'exists:App\Models\Resolution,code'],
			'client' => ['required', 'exists:App\Models\Client,document'],
			'expirationDate' => ['required', 'date'],
			'description' => ['required', 'string', 'max:1000'],
			'wayPay' => ['required', 'exists:App\Models\WayPayment,id'],
			'payMethod' => ['required', 'exists:App\Models\PaymentMethod,id'],
			'bankAccount' => ['nullable', 'string', 'max:200'],
			'items' => ['required'],
			'items.*.productCode' => ['required', 'string', 'max:50'],
			'items.*.productName' => ['required', 'string', 'max:500'],
			'items.*.productBrand' => ['nullable', 'string', 'max:100'],
			'items.*.productAmount' => ['required', 'numeric', 'min:1'],
			'items.*.productPrice' => ['required', 'numeric', 'min:1'],
			'items.*.productDiscount' => ['numeric', 'min:1', 'nullable'],
		];
	}

	public function messages()
	{
		return [
			'prefix.required' => 'El prefijo es requerido',
			'prefix.exists' => 'El prefijo no existe',
			'client.required' => 'El tercero es requerido',
			'client.exists' => 'El tercero no existe',
			'expirationDate.required' => 'La fecha de vencimiento es requerida',
			'expirationDate.date' => 'La fecha de vencimiento no es valida',
			'description.required' => 'La descripcion es requerida',
			'description.string' => 'La descripcion debe ser texto',
			'description.max' => 'La descripcion tiene un maximo de 1000 caracteres',
			'wayPay.required' => 'La forma de pago es requerida',
			'wayPay.exists' => 'La forma de pago no existe',
			'payMethod.required' => 'El metodo de pago es requerida',
			'payMethod.exists' => 'El metodo de pago no existe',
			'bankAccount.string' => 'La cuenta bancaria debe ser texto',
			'bankAccount.nullable' => 'La cuenta bancaria no es requerida',
			'bankAccount.max' => 'La cuenta bancaria tiene un maximo de 200 caracteres',
			'items.required' => 'El datalle es requerido',
			'items.*.productCode.required' => 'El codigo de item debe ser requerido',
			'items.*.productCode.string' => 'El codigo de item debe ser texto',
			'items.*.productCode.max' => 'El codigo de item tiene un maximo de 50 caracteres',

			'items.*.productName.required' => 'El nombre de item debe ser requerido',
			'items.*.productName.string' => 'El nombre de item debe ser texto',
			'items.*.productName.max' => 'El nombre de item tiene un maximo de 500 caracteres',

			'items.*.productBrand.nullable' => 'La marca de item no es requerido',
			'items.*.productBrand.string' => 'La marca de item debe ser texto',
			'items.*.productBrand.max' => 'La marca de item tiene un maximo de 100 caracteres',

			'items.*.productAmount.required' => 'La cantidad de item es requerido',
			'items.*.productAmount.numeric' => 'La cantidad de item debe ser numerico',
			'items.*.productAmount.min' => 'La cantidad de item tiene un minimo de 1',

			'items.*.productPrice.required' => 'El precio de item es requerido',
			'items.*.productPrice.numeric' => 'El precio de item debe ser numerico',
			'items.*.productPrice.min' => 'El precio de item tiene un minimo de 1',

			'items.*.productDiscount.nullable' => 'El descuento de item no es requerido',
			'items.*.productDiscount.numeric' => 'El descuento de item debe ser numerico',
			'items.*.productDiscount.min' => 'El descuento de item tiene un minimo de 1',

		];
	}


	protected function failedValidation(Validator $validator)
	{
		$response = new JsonResponse([
			'errors'  => $validator->errors()
		], 422);

		throw new ValidationException($validator, $response);
	}
}
