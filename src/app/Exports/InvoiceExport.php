<?php

namespace App\Exports;

use App\Models\InvoiceHeader;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
{
	use Exportable;
	public function __construct($request)
	{
		$this->request = $request;
	}

	public function query()
	{
		$data = InvoiceHeader::query()->select('invoice_headers.code', 'resolutions.prefix', 'invoice_headers.consecutive', 'providers.office_name as providerName', 'providers.document as providerDocument', 'invoice_headers.description', 'invoice_headers.expiration_date',  'clients.name as clientName', 'clients.last_name as clientLastName', 'clients.document as clientDocument', 'institutions.name as institution', 'dependences.name as dependence',  'way_payments.name as wayPay', 'payment_methods.name as payMethod', 'invoice_headers.bank_account', 'invoice_headers.total')
			->leftJoin('invoice_responses', 'invoice_responses.invoice_headers_id', '=', 'invoice_headers.id')
			->join('clients', 'invoice_headers.clients_id', '=', 'clients.id')
			->join('resolutions', 'invoice_headers.code', '=', 'resolutions.code')
			->join('dependences', 'resolutions.dependences_id', '=', 'dependences.id')
			->join('institutions', 'dependences.institutions_id', '=', 'institutions.id')
			->join('providers', 'institutions.providers_id', '=', 'providers.id')
			->join('way_payments', 'way_payments.id', '=', 'invoice_headers.way_payments_id')
			->join('payment_methods', 'payment_methods.id', '=', 'invoice_headers.payment_methods_id')
			->orderByDesc('invoice_headers.updated_at');
		if ($this->request['dateStart'] && $this->request['dateEnd']) {
			$data->whereBetween('invoice_headers.updated_at', [$this->request['dateStart'] . ' 00:00:00', $this->request['dateEnd'] . ' 23:59:59']);
		} else if ($this->request['dateStart']) {
			$data->whereDate('invoice_headers.updated_at',  date($this->request['dateStart']));
		} else if ($this->request['dateEnd']) {
			$data->whereDate('invoice_headers.updated_at',  date($this->request['dateEnd']));
		}
		if ($this->request['consecutive']) {
			$data->where('invoice_headers.consecutive', $this->request['consecutive']);
		}
		if ($this->request['client']) {
			$data->whereIn('clients.document', $this->request['client']);
		}
		if ($this->request['prefix']) {
			if (is_numeric($this->request['prefix'])) {
				$data->where('resolutions.prefix', $this->request['prefix']);
			} else {
				$data->where('invoice_headers.code', $this->request['prefix']);
			}
		}
		return $data;
	}
	public function headings(): array
	{
		return [
			'Codigo interno',
			'Prefijo factura',
			'Consecutivo',
			'Nombre del proveedor',
			'Documento del proveedor',
			'Descripcion',
			'Fecha de vencimiento',
			'Nombres del cliente',
			'Apellidos del cliente',
			'Documento del cliente',
			'Institucion',
			'Dependencia',
			'Forma de pago',
			'Metodo de pago',
			'Cuenta bancaria',
			'Total',
		];
	}
	public function styles(Worksheet $sheet)
	{
		return [
			// Style the first row as bold text.
			1    => ['font' => ['bold' => true]],
		];
	}
}
