<?php

namespace App\Exports;

use App\Models\Note;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NoteExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles
{
	use Exportable;
	public function __construct($request)
	{
		$this->request = $request;
	}

	public function query()
	{
		$data = Note::query()->select('notes.code', 'notes.consecutive', 'providers.office_name as providerName', 'providers.document as providerDocument', 'notes.description', 'type_notes.name as type_note', 'note_concepts.name as concept', 'invoice_headers.code as invoiceCode',  'invoice_headers.consecutive as invoiceConsecutive', 'notes.total')
			->join('note_concepts', 'note_concepts.id', '=', 'notes.note_concepts_id')
			->join('type_notes', 'type_notes.id', '=', 'note_concepts.type_notes_id')
			->join('invoice_headers', 'invoice_headers.id', '=', 'notes.invoice_headers_id')
			->join('resolutions', 'invoice_headers.code', '=', 'resolutions.code')
			->join('dependences', 'resolutions.dependences_id', '=', 'dependences.id')
			->join('institutions', 'dependences.institutions_id', '=', 'institutions.id')
			->join('providers', 'institutions.providers_id', '=', 'providers.id')
			->leftJoin('note_responses', 'note_responses.notes_id', '=', 'notes.id')
			->orderByDesc('notes.updated_at');
		if ($this->request['dateStart'] && $this->request['dateEnd']) {
			$data->whereBetween('notes.updated_at', [$this->request['dateStart'] . ' 00:00:00', $this->request['dateEnd'] . ' 23:59:59']);
		} else if ($this->request['dateStart']) {
			$data->whereDate('notes.updated_at',  date($this->request['dateStart']));
		} else if ($this->request['dateEnd']) {
			$data->whereDate('notes.updated_at',  date($this->request['dateEnd']));
		}

		if ($this->request['client']) {
			$data->where('clients.document', $this->request['client']);
		}
		if ($this->request['consecutiveInvoice']) {
			$data->where('invoice_headers.consecutive', $this->request['consecutiveInvoice']);
		}
		if ($this->request['consecutiveNote']) {
			$data->where('notes.consecutive', $this->request['consecutiveNote']);
		}
		if ($this->request['prefixInvoice']) {
			if (is_numeric($this->request['prefixInvoice'])) {
				$data->where('resolutions.prefix', $this->request['prefixInvoice']);
			} else {
				$data->where('invoice_headers.code', $this->request['prefixInvoice']);
			}
		}
		return $data;
	}
	public function headings(): array
	{
		return [
			'Codigo',
			'Consecutivo',
			'Nombre del proveedor',
			'Documento del proveedor',
			'Descripcion',
			'Tipo',
			'Concepto',
			'Prefijo interno de factura',
			'Consecutivo de factura',
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
