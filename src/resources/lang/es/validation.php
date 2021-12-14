<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

	'accepted' => 'El :attribute debe ser aceptado.',
	'active_url' => 'El :attribute no es una URL válida.',
	'after' => 'El :attribute debe ser una fecha posterior a :date.',
	'after_or_equal' => 'El :attribute debe ser una fecha posterior o igual a :date.',
	'alpha' => 'El :attribute sólo debe contener letras.',
	'alpha_dash' => 'El :attribute sólo debe contener letras, números, guiones y guiones bajos.',
	'alpha_num' => 'El :attribute sólo debe contener letras y números.',
	'array' => 'El :attribute debe ser un array.',
	'before' => 'El :attribute debe ser una fecha anterior a :date.',
	'before_or_equal' => 'El :attribute debe ser una fecha anterior o igual a :date.',
	'between' => [
		'numeric' => 'El :attribute debe estar entre :min y :max.',
		'file' => 'El :attribute debe estar entre :min y :max kilobytes.',
		'string' => 'El :attribute debe estar entre :min y :max caracteres.',
		'array' => 'El :attribute debe tener entre :min y :max items.',
	],
	'boolean' => 'El campo :attribute debe ser verdadero o falso.',
	'confirmed' => 'La confirmación del :attribute no coincide.',
	'date' => 'El :attribute no es una fecha válida.',
	'date_equals' => 'El :attribute debe ser una fecha igual a :date.',
	'date_format' => 'El :attribute no coincide con el formato :format.',
	'different' => 'El :attribute y :other deben ser diferentes.',
	'digits' => 'El :attribute debe ser :digits un dígito.',
	'digits_between' => 'El :attribute debe estar entre :min y :max dígitos.',
	'dimensions' => 'El :attribute tiene dimensiones de imagen no válidas.',
	'distinct' => 'El campo :attribute tiene un valor duplicado.',
	'email' => 'El :attribute debe ser una dirección de correo electrónico válida.',
	'ends_with' => 'El :attribute debe terminar con uno de los siguientes: :values.',
	'exists' => 'La selección :attribute no es válida.',
	'file' => 'El :attribute debe ser un archivo.',
	'filled' => 'El campo del :attribute debe tener un valor.',
	'gt' => [
		'numeric' => 'El :attribute debe ser mayor que :value.',
		'file' => 'El :attribute debe ser mayor que :value kilobytes.',
		'string' => 'El :attribute debe ser mayor que :value caracteres.',
		'array' => 'The :attribute must have more than :value items.',
	],
	'gte' => [
		'numeric' => 'El :attribute debe ser mayor o igual que :value.',
		'file' => 'El :attribute debe ser mayor o igual que :value kilobytes.',
		'string' => 'El :attribute debe ser mayor o igual que :value caracteres.',
		'array' => 'El :attribute debe tener :value items o mas.',
	],
	'image' => 'El :attribute debe ser una imagen.',
	'in' => 'La selección :attribute no es válida.',
	'in_array' => 'El campo :attribute no existe en :other.',
	'integer' => 'El :attribute debe ser un número entero.',
	'ip' => 'El :attribute debe ser una dirección IP válida.',
	'ipv4' => 'El :attribute debe ser una dirección IPv4 válida',
	'ipv6' => 'El :attribute debe ser una dirección IPv6 válida.',
	'json' => 'El :attribute debe ser una cadena JSON válida.',
	'lt' => [
		'numeric' => 'The :attribute must be less than :value.',
		'file' => 'The :attribute must be less than :value kilobytes.',
		'string' => 'The :attribute must be less than :value characters.',
		'array' => 'The :attribute must have less than :value items.',
	],
	'lte' => [
		'numeric' => 'The :attribute must be less than or equal :value.',
		'file' => 'The :attribute must be less than or equal :value kilobytes.',
		'string' => 'The :attribute must be less than or equal :value characters.',
		'array' => 'The :attribute must not have more than :value items.',
	],
	'max' => [
		'numeric' => 'The :attribute must not be greater than :max.',
		'file' => 'The :attribute must not be greater than :max kilobytes.',
		'string' => 'The :attribute must not be greater than :max characters.',
		'array' => 'The :attribute must not have more than :max items.',
	],
	'mimes' => 'The :attribute must be a file of type: :values.',
	'mimetypes' => 'The :attribute must be a file of type: :values.',
	'min' => [
		'numeric' => 'The :attribute must be at least :min.',
		'file' => 'The :attribute must be at least :min kilobytes.',
		'string' => 'The :attribute must be at least :min characters.',
		'array' => 'The :attribute must have at least :min items.',
	],
	'multiple_of' => 'The :attribute must be a multiple of :value.',
	'not_in' => 'The selected :attribute is invalid.',
	'not_regex' => 'The :attribute format is invalid.',
	'numeric' => 'The :attribute must be a number.',
	'password' => 'The password is incorrect.',
	'present' => 'The :attribute field must be present.',
	'regex' => 'The :attribute format is invalid.',
	'required' => 'El campo :attribute es requerido.',
	'required_if' => 'The :attribute field is required when :other is :value.',
	'required_unless' => 'The :attribute field is required unless :other is in :values.',
	'required_with' => 'The :attribute field is required when :values is present.',
	'required_with_all' => 'The :attribute field is required when :values are present.',
	'required_without' => 'The :attribute field is required when :values is not present.',
	'required_without_all' => 'The :attribute field is required when none of :values are present.',
	'prohibited' => 'The :attribute field is prohibited.',
	'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
	'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
	'same' => 'The :attribute and :other must match.',
	'size' => [
		'numeric' => 'The :attribute must be :size.',
		'file' => 'The :attribute must be :size kilobytes.',
		'string' => 'The :attribute must be :size characters.',
		'array' => 'The :attribute must contain :size items.',
	],
	'starts_with' => 'The :attribute must start with one of the following: :values.',
	'string' => 'The :attribute must be a string.',
	'timezone' => 'The :attribute must be a valid zone.',
	'unique' => 'The :attribute has already been taken.',
	'uploaded' => 'The :attribute failed to upload.',
	'url' => 'The :attribute format is invalid.',
	'uuid' => 'The :attribute must be a valid UUID.',

	/*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

	'custom' => [
		'attribute-name' => [
			'rule-name' => 'custom-message',
		],
	],

	/*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

	'attributes' => [
		'prefix' => 'Prefijo'
	],

];
