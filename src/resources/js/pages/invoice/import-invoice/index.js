import React, { useEffect, useState } from "react";
import { Button, Form, Modal, Spinner } from "react-bootstrap";
import CSVReader from "react-csv-reader";
import DataTable from "react-data-table-component";
import { toast } from "react-toastify";
import datatableSpanish from "../../../components/static/datatable-locale.json";
import ExpandableInvoiceComponent from "../../../components/forms/ExpandableInvoiceComponent";

const papaparseOptions = {
	header: false,
	dynamicTyping: true,
	skipEmptyLines: true,
	delimiter: ";",
};
const columns = [
	{
		name: "Prefijo",
		sortable: true,
		selector: "prefix",
		grow: 1,
	},
	{
		name: "Descripcion",
		sortable: true,
		selector: "description",
		grow: 1,
	},
	{
		name: "Tercero",
		sortable: true,
		selector: "client",
		grow: 1,
	},
	{
		name: "Fecha de vencimiento",
		sortable: true,
		selector: "expirationDate",
		grow: 1,
	},
	{
		name: "Forma de pago",
		sortable: true,
		selector: "wayPay",
		grow: 1,
	},
	{
		name: "Metodo de pago",
		sortable: true,
		selector: "payMethod",
		grow: 1,
	},
	{
		name: "Cuenta Bancaria",
		sortable: true,
		selector: "bankAccount",
		grow: 1,
	},
	{
		name: "Total",
		sortable: true,
		selector: "total",
		grow: 1,
	},
];

export default function ImportInvoice() {
	const [viewInvoices, setViewInvoices] = useState(false);
	const [successInvoice, setSuccessInvoice] = useState(false);
	const [invoices, setinvoices] = useState([]);
	const [show, setShow] = useState(false);
	const [dataModal, setDataModal] = useState([]);
	const handleClose = () => {
		if (successInvoice == true) {
			setinvoices([]);
			setViewInvoices(false);
		}
		setShow(false);
	};
	const handleShow = () => setShow(true);

	useEffect(() => {
		if (invoices.length) {
			toast.dismiss();
			toast.info("Facturas cargadas correctamente");
			setViewInvoices(true);
		}
	}, [invoices]);

	function handleLoad(data, info) {
		orderInvoices(data);
	}

	function orderInvoices(data) {
		var keyOrder = [];
		var order = [];
		var total = [];
		toast.dismiss();
		let columns = 15
		data.map((items, index) => {
			if (items.length < columns) {
				toast.error(`Columnas Insuficientes en la fila: ${index + 1} , Columnas necesarias: ${columns}`);
				return false;
			}
			let filter = keyOrder.indexOf(items[0]);
			if (filter != -1) {
				order[filter]["items"].push({
					productCode: items[8],
					productName: items[9],
					productBrand: items[10],
					productAmount: items[11],
					productPrice: items[12],
					productDiscount: items[13],
				});
				total[filter] += items[11] * items[12] - items[13];
			} else {
				order.push({
					id: items[0],
					prefix: items[1],
					description: items[2],
					client: items[3],
					expirationDate: items[4],
					wayPay: items[5],
					payMethod: items[6],
					bankAccount: items[7],
					total: 0,
					items: [
						{
							productCode: items[8],
							productName: items[9],
							productBrand: items[10],
							productAmount: items[11],
							productPrice: items[12],
							productDiscount: items[13],
							productReasonDiscount: items[14],
						},
					],
				});
				let indexOrder = keyOrder.push(items[0]);
				total[indexOrder - 1] = +items[11] * items[12] - items[13];
			}
		});

		total.forEach((item, index) => {
			order[index]["total"] = item;
		});
		setinvoices(order);
	}

	function handleValidate() {
		toast.dismiss();
		toast.info("Validando...");
		const response = axios.post("api/invoices/validate", invoices)
			.then((res) => {
				if (res.status == 200) {
					toast.dismiss();
					toast.success("Factura sin errores");
					setDataModal([]);
					return true;
				}
			})
			.catch((err) => {
				if (err.response.status == 400) {
					toast.dismiss();
					toast.error("Errores detectados");
					setDataModal(err.response.data.data);
					handleShow();
				} else {
					toast.dismiss();
					toast.error(err.response.data.message);
				}
				return false;
			});
		return response;
	}

	function handleError(data) {
		console.error(data);
	}

	function handleCancel() {
		toast.dismiss();
		toast.warning("Importacion cancelada");
		setinvoices([]);
		setViewInvoices(false);
	}

	async function handleSave() {
		const validate = await handleValidate();

		if (validate === true) {
			toast.dismiss();
			toast.warning("Importando");
			await importInvoice();
		}
	}

	function importInvoice() {
		axios.post("api/invoices/import", invoices)
			.then((res) => {
				if (res.status == 200) {
					toast.dismiss();
					toast.success("Facturas importadas correctamente");
					setSuccessInvoice(true);
					setDataModal(res.data.data);
					handleShow();
				}
			})
			.catch((err) => {
				toast.dismiss();
				toast.error("Error al guardar");
			});
	}

	return (
		<div className="my-5 mx-5">
			{viewInvoices == false && (
				<div className="col-12">
					<h5 className="text-center">
						Cargue sus facturas mediante un archivo de extencion{" "}
						<strong>CSV</strong> usando el delimitador{" "}
						<strong>;</strong>
					</h5>

					<br />
					<div className="col-6 custom-file col-4 offset-3 ">
						<CSVReader
							fileEncoding="ISO-8859-1"
							cssInputClass="custom-file-input"
							onFileLoaded={handleLoad}
							parserOptions={papaparseOptions}
							onError={handleError}
						/>
						<label
							className="custom-file-label"
							htmlFor="customFile"
						>
							Seleccionar un archivo...
						</label>
					</div>
				</div>
			)}
			{viewInvoices == true && (
				<DataTable
					subHeaderComponent={
						<div className="row col-12 d-flex justify-content-center">
							<button
								className="btn btn-success col-3"
								onClick={handleSave}
							>
								<i className="far fa-save mr-2 my-auto" />{" "}
								Enviar
							</button>
							<button
								className="btn btn-warning col-3 mx-4"
								onClick={handleValidate}
							>
								<i className="fas fa-search mr-2 my-auto" />{" "}
								Validar
							</button>
							<button
								className="btn btn-danger col-3"
								onClick={handleCancel}
							>
								<i className="far fa-times-circle mr-2 my-auto" />{" "}
								Cancelar
							</button>
						</div>
					}
					className="my-5"
					fixedHeader={true}
					subHeader
					fixedHeaderScrollHeight="50vh"
					expandableRows
					expandableRowsComponent={
						<ExpandableInvoiceComponent import={true} />
					}
					highlightOnHover
					noDataComponent={
						<div className="text-secondary my-5">
							No hay datos que mostrar
						</div>
					}
					striped
					responsive
					persistTableHead
					progressComponent={
						<Spinner
							style={{ margin: 50 }}
							variant="primary"
							animation="border"
						>
							<span className="sr-only">Loading...</span>
						</Spinner>
					}
					pagination
					title=""
					columns={columns}
					data={invoices}
					paginationComponentOptions={datatableSpanish}
				/>
			)}
			<Modal
				scrollable={true}
				size="lg"
				show={show}
				onHide={handleClose}
				keyboard={false}
			>
				<Modal.Header closeButton>
					<Modal.Title>
						{successInvoice == true
							? "Facturas Importadas"
							: "Errores Detectados"}
					</Modal.Title>
				</Modal.Header>
				<Modal.Body>
					<ul className="list-group">
						{Object.values(dataModal).map((e) => {
							return (
								<li key={e} className="list-group-item">
									{e}
								</li>
							);
						})}
					</ul>
				</Modal.Body>
				<Modal.Footer>
					<Button variant="danger" onClick={handleClose}>
						Cerrar
					</Button>
				</Modal.Footer>
			</Modal>
		</div>
	);
}
