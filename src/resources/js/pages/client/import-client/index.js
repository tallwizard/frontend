import React, { useEffect, useState } from "react";
import { Button, Form, Modal, Spinner } from "react-bootstrap";
import CSVReader from "react-csv-reader";
import DataTable from "react-data-table-component";
import { toast } from "react-toastify";
import datatableSpanish from "../../../components/static/datatable-locale.json";

const papaparseOptions = {
	header: false,
	dynamicTyping: true,
	skipEmptyLines: true,
	delimiter: ";",
};
const columns = [
	{
		name: "Nombres",
		sortable: true,
		selector: "name",
	},
	{
		name: "Apellidos",
		sortable: true,
		selector: "lastName",
	},
	{
		name: "Tipo de tercero",
		sortable: true,
		selector: "typeClient",
	},
	{
		name: "Tipo de documento",
		sortable: true,
		selector: "typeDocument",
	},
	{
		name: "Documento",
		sortable: true,
		selector: "document",
	},
	{
		name: "Telefono",
		sortable: true,
		selector: "phone",
	},
	{
		name: "Correo",
		sortable: true,
		selector: "email",
	},
	{
		name: "Ciudad",
		sortable: true,
		selector: "city",
	},
	{
		name: "Direccion",
		sortable: true,
		selector: "address",
	},
];

export default function ImportClient() {
	const [viewClients, setViewClients] = useState(false);
	const [successClient, setSuccessClient] = useState(false);
	const [clients, setClients] = useState([]);
	const [show, setShow] = useState(false);
	const [dataModal, setDataModal] = useState([]);
	const handleClose = () => {
		if (successClient == true) {
			setClients([]);
			setViewClients(false);
		}
		setShow(false);
	};
	const handleShow = () => setShow(true);

	useEffect(() => {
		if (clients.length) {
			toast.dismiss();
			toast.info("Terceros cargados correctamente");
			setViewClients(true);
		}
	}, [clients]);

	function handleLoad(data, info) {
		orderClients(data);
	}

	function orderClients(data) {
		var order = [];
		let columns = 9
		data.map((items, index) => {
			if (items.length < columns || items.length > columns) {
				toast.dismiss();
				toast.error(`Columnas Insuficientes en la fila: ${index + 1} , Columnas necesarias: ${columns}`, { autoClose: false, });
				return false;
			}
			order.push({
				name: items[0],
				lastName: items[1],
				typeClient: items[2],
				typeDocument: items[3],
				document: items[4],
				phone: items[5],
				email: items[6],
				city: items[7],
				address: items[8],
			});
		});
		setClients(order);
	}

	async function handleValidate() {
		toast.dismiss();
		toast.info("Validando...");
		await axios.post("api/clients/validate", clients)
			.then((res) => {
				if (res.status == 200) {
					toast.dismiss();
					toast.success("Terceros sin errores");
					setDataModal([]);
				}
			})
			.catch((err) => {
				if (err.response.status == 400) {
					toast.dismiss();
					toast.error("Errores detectados");
					setDataModal(err.response.data.message);
					handleShow();
				} else {
					toast.dismiss();
					toast.error(err.response);
				}
			});
	}

	function handleError(data) {
		console.error(data);
	}

	function handleCancel() {
		toast.dismiss();
		toast.warning("Importacion cancelada");
		setClients([]);
		setViewClients(false);
	}

	async function handleSave() {
		await handleValidate();
		if (dataModal.length == 0) {
			toast.dismiss();
			toast.warning("Importando...");
			await importInvoice();
		}
	}

	async function importInvoice() {
		await axios.post("api/clients/import", clients)
			.then((res) => {
				if (res.status == 200) {
					toast.dismiss();
					toast.success("Terceros importados correctamente");
					setClients([]);
					setViewClients(false);
				}
			})
			.catch((err) => {
				toast.dismiss();
				toast.error("Error al guardar");
			});
	}

	return (
		<div className="my-5 mx-5">
			{viewClients == false && (
				<div className="col-12">
					<h5 className="text-center">
						Cargue los terceros mediante un archivo de extencion{" "}
						<strong>CSV</strong> usando el delimitador{" "}
						<strong>;</strong>
					</h5>

					<br />
					<div className="col-6 custom-file col-4 offset-3 ">
						<CSVReader
							fileEncoding="UTF-8"
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
			{viewClients == true && (
				<DataTable
					subHeaderComponent={
						<div className="row col-12 d-flex justify-content-center">
							<button
								className="btn btn-success col-3"
								onClick={handleSave}
							>
								<i className="far fa-save mr-2 my-auto" />{" "}
								Guardar
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
					data={clients}
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
						{successClient == true
							? "Facturas Importadas"
							: "Errores Detectados"}
					</Modal.Title>
				</Modal.Header>
				<Modal.Body>
					<ul className="list-group">
						{Object.values(dataModal).map((row) => {
							return row.map((item) => {
								return (
									<li key={item} className="list-group-item">
										{item}
									</li>
								);
							});
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
