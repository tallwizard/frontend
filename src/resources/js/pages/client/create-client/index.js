import React, { Component } from "react";
import { toast } from "react-toastify";
import DataTable from "react-data-table-component";
import { Modal, Spinner } from "react-bootstrap";
import datatableSpanish from "../../../components/static/datatable-locale.json";
import ClientForm from "../../../components/forms/ClientForm";
import ModalDialog from "../../../components/ModalDialog";
const initialValues = {
	id: "",
	name: "",
	lastName: "",
	typeClient: "",
	typeDocument: "",
	document: "",
	phone: "",
	email: "",
	city: "",
	address: "",
};

export default class CreateClient extends Component {
	constructor(props) {
		super(props);
		this.state = {
			typeClientData: [],
			typeDocumentData: [],
			cityData: [],
			data: [],
			loading: true,
			showModal: false,
			showModalDelete: false,
			titleModal: "Añadir Datos",
			dataModal: initialValues,
			dataModalDelete: {
				title: "Eliminar Dato",
				body: "¿Esta seguro que desea eliminar este elemento?",
			},
			columns: [
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
					name: "Departamento",
					sortable: true,
					selector: "departament",
				},
				{
					name: "Ciudad",
					sortable: true,
					selector: "city",
				},
				{
					name: "Codigo postal",
					sortable: true,
					selector: "zipCode",
				},
				{
					name: "Direccion",
					sortable: true,
					selector: "address",
				},
				{
					name: "Acciones",
					cell: (row) => (
						<div className="row col-12">
							<button
								className="btn btn-secondary col-6"
								onClick={() => this.openModal(row.id)}
							>
								<i className="fas fa-edit"></i>
							</button>
							<button
								className="btn btn-primary col-6"
								onClick={() => this.openModalDelete(row.id)}
							>
								<i className="fas fa-trash"></i>
							</button>
						</div>
					),
					ignoreRowClick: true,
					allowOverflow: true,
					button: true,
				},
			],
		};
	}

	fetchData() {
		axios
			.get("api/clients")
			.then((res) => {
				if (res.status == 200) {
					this.setState({
						data: res.data.data,
						loading: false,
					});
				}
			})
			.catch((err) => {
				this.setState({
					data: [],
					loading: false,
				});
				if (err.response.status != 404) {
					toast.dismiss();
					toast.error(err.response.data.message, {
						autoClose: false,
					});
				}
			});
		axios
			.get("api/resource/client")
			.then((res) => {
				if (res.status == 200) {
					this.setState({
						typeClientData: res.data.data,
					});
				}
			})
			.catch((err) => {
				this.setState({
					typeClientData: [],
				});
				if (err.response.status != 404) {
					toast.dismiss();
					toast.error(err.response.data.message, {
						autoClose: false,
					});
				}
			});
		axios
			.get("api/resource/document")
			.then((res) => {
				if (res.status == 200) {
					this.setState({
						typeDocumentData: res.data.data,
					});
				}
			})
			.catch((err) => {
				this.setState({
					typeDocumentData: res.data.data,
				});
				if (err.response.status != 404) {
					toast.dismiss();
					toast.error(err.response.data.message, {
						autoClose: false,
					});
				}
			});
		axios
			.get("api/resource/city")
			.then((res) => {
				if (res.status == 200) {
					this.setState({
						cityData: res.data.data,
					});
				}
			})
			.catch((err) => {
				this.setState({
					cityData: [],
				});
				if (err.response.status != 404) {
					toast.dismiss();
					toast.error(err.response.data.message, {
						autoClose: false,
					});
				}
			});
	}

	componentDidMount() {
		this.fetchData();
	}

	openModalDelete(event) {
		this.setState({
			showModalDelete: true,
			dataIdModalDelete: event,
		});
	}

	async openModal(event) {
		if (event) {
			await axios
				.get("api/clients/" + event)
				.then((res) => {
					this.setState({
						titleModal: "Editar Datos",
						dataModal: res.data.data,
					});
				})
				.catch((err) => {
					toast.dismiss();
					toast.error(err.response.data.message, {
						autoClose: false,
					});
				});
		} else {
			this.setState({
				titleModal: "Añadir Datos",
				dataModal: initialValues,
			});
		}
		this.setState({ showModal: true });
	}

	closeModal() {
		this.fetchData();
		this.setState({ showModal: false });
	}
	closeModalDelete() {
		this.fetchData();
		this.setState({ showModalDelete: false });
	}
	async acceptModalDelete() {
		await axios
			.get("api/clients/delete/" + this.state.dataIdModalDelete)
			.then((res) => {
				this.closeModalDelete();
				toast.dismiss();
				toast.success(res.data.message);
			})
			.catch((err) => {
				toast.dismiss();
				toast.error(err.response.data.message, {
					autoClose: false,
				});
			});
	}
	render() {
		return (
			<>
				<DataTable
					actions={
						<button
							className="btn btn-success col-3"
							onClick={() => this.openModal()}
						>
							<i className="far fa-save mr-2 my-auto" />{" "}
							A&ntilde;adir
						</button>
					}
					className="my-5"
					fixedHeader={true}
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
					progressPending={this.state.loading}
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
					columns={this.state.columns}
					data={this.state.data}
					paginationComponentOptions={datatableSpanish}
				/>
				<Modal
					scrollable={true}
					size="md"
					show={this.state.showModal}
					onHide={() => this.closeModal()}
					keyboard={false}
				>
					<Modal.Header closeButton>
						<Modal.Title>{this.state.titleModal}</Modal.Title>
					</Modal.Header>
					<Modal.Body>
						<ClientForm
							cityData={this.state.cityData}
							typeDocumentData={this.state.typeDocumentData}
							typeClientData={this.state.typeClientData}
							dataModal={this.state.dataModal}
							closeModal={() => this.closeModal()}
						/>
					</Modal.Body>
				</Modal>
				<ModalDialog
					data={this.state.dataModalDelete}
					show={this.state.showModalDelete}
					closeModal={() => this.closeModalDelete()}
					accept={() => this.acceptModalDelete()}
				/>
			</>
		);
	}
}
