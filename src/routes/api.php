<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DependenceController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ResolutionController;
use App\Http\Controllers\SoftwareDataController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;

Route::post("/login", [UserController::class, 'login']);
Route::post("/register", [UserController::class, 'register']);


Route::middleware('auth:api')->group(function () {
	Route::post("/validate/token", [UserController::class, 'validateToken']);
	Route::post("/logout", [UserController::class, 'logout']);

	Route::prefix('user')->group(function () {
		Route::post('/update', [UserController::class, 'update']);
		Route::get('/delete/{id}', [UserController::class, 'delete']);
		Route::get('/list/{id?}', [UserController::class, 'listUsers']);
		Route::post("/create", [UserController::class, 'create']);
		Route::get("/roles", [UserController::class, 'getRoles']);
	});

	Route::prefix('software')->group(function () {
		Route::get('/{id?}', [SoftwareDataController::class, 'index']);
		Route::post('/update', [SoftwareDataController::class, 'update']);
		Route::get('/delete/{id}', [SoftwareDataController::class, 'delete']);
		Route::post('/create', [SoftwareDataController::class, 'create']);
	});

	Route::prefix('institution')->group(function () {
		Route::get('/{id?}', [InstitutionController::class, 'index']);
		Route::post('/update', [InstitutionController::class, 'update']);
		Route::get('/delete/{id}', [InstitutionController::class, 'delete']);
		Route::post('/create', [InstitutionController::class, 'create']);
	});

	Route::prefix('dependence')->group(function () {
		Route::get('/{id?}', [DependenceController::class, 'index']);
		Route::post('/update', [DependenceController::class, 'update']);
		Route::get('/delete/{id}', [DependenceController::class, 'delete']);
		Route::post('/create', [DependenceController::class, 'create']);
	});

	Route::prefix('resolution')->group(function () {
		Route::get('/{id?}', [ResolutionController::class, 'index']);
		Route::post('/update', [ResolutionController::class, 'update']);
		Route::get('/delete/{id}', [ResolutionController::class, 'delete']);
		Route::post('/create', [ResolutionController::class, 'create']);
	});

	Route::prefix('provider')->group(function () {
		Route::get('/{id?}', [ProviderController::class, 'index']);
		Route::post('/update', [ProviderController::class, 'update']);
		Route::get('/delete/{id}', [ProviderController::class, 'delete']);
		Route::post('/create', [ProviderController::class, 'create']);
	});

	Route::prefix('invoices')->group(function () {
		Route::get('/delete/{id}', [InvoiceController::class, 'delete']);
		Route::post('/balance', [InvoiceController::class, 'getBalance']);
		Route::get('/loading', [InvoiceController::class, 'loadingInvoice']);
		Route::get('/prefix', [InvoiceController::class, 'getPrefix']);
		Route::get('/client/{id?}', [InvoiceController::class, 'getClient']);
		Route::get('/paymethod', [InvoiceController::class, 'getPaymentMethods']);
		Route::get('/waypay', [InvoiceController::class, 'getWayPayments']);
		Route::post('/create', [InvoiceController::class, 'create']);
		Route::post('/import', [InvoiceController::class, 'importInvoice']);
		Route::post('/validate', [InvoiceController::class, 'validateInvoice']);
		Route::post('/consult', [InvoiceController::class, 'consultInvoice']);
		Route::get('/detail/{id?}', [InvoiceController::class, 'consultDetail']);
		Route::post('/file', [InvoiceController::class, 'printFile']);
	});

	Route::prefix('notes')->group(function () {
		Route::get('/delete/{id}', [NoteController::class, 'delete']);
		Route::get('/loading', [NoteController::class, 'loadingNote']);
		Route::get('/concept', [NoteController::class, 'getConcept']);
		Route::post('/create', [NoteController::class, 'create']);
		Route::post('/validate/invoice', [NoteController::class, 'validateInvoice']);
		Route::post('/consult', [NoteController::class, 'consultNote']);
	});

	Route::prefix('clients')->group(function () {
		Route::get('/autocomplete/{data?}', [ClientController::class, 'autocompleteClient']);
		Route::get('/{id?}', [ClientController::class, 'consultIndex']);
		Route::post('/update', [ClientController::class, 'update']);
		Route::get('/delete/{id}', [ClientController::class, 'delete']);
		Route::post('/create', [ClientController::class, 'create']);
		Route::post('/import', [ClientController::class, 'importData']);
		Route::post('/validate', [ClientController::class, 'validateData']);
		Route::post('/consult', [ClientController::class, 'consultClient']);
	});

	Route::prefix('resource')->group(function () {
		Route::get('/client/{id?}', [ClientController::class, 'getTypeClient']);
		Route::get('/regime/{id?}', [ClientController::class, 'getTypeRegime']);
		Route::get('/document/{id?}', [ClientController::class, 'getTypeDocument']);
		Route::get('/city/{id?}', [ClientController::class, 'getCity']);
	});

	Route::prefix('export')->group(function () {
		Route::post('/client', [ExportController::class, 'exportClient']);
		Route::post('/invoice', [ExportController::class, 'exportInvoice']);
		Route::post('/note', [ExportController::class, 'exportNote']);
	});

	Route::get('/help', function () {
		$filename = 'help.pdf';
		$path = storage_path($filename);
		return Response::make(file_get_contents($path), 200, [
			'Content-Type' => 'application/pdf',
			'Content-Disposition' => 'inline; filename="' . $filename . '"'
		]);
	})->name('help');
});
