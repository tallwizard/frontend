import React, { Component } from "react";
import { toast } from "react-toastify";
import DataTable from "react-data-table-component";
import { Modal, Spinner } from "react-bootstrap";
import datatableSpanish from "../../components/static/datatable-locale.json";
import SoftwareForm from "../../components/forms/SoftwareForm";
import ModalDialog from "../../components/ModalDialog";
const initialValues = {
	id: "",
	name: "",
	pin: "",
	identification: "",
	test_id: "",
};

export default class Software extends Component {
	constructor(props) {
		super(props);
		this.state = {
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
					name: "Nombre",
					sortable: true,
					selector: "name",
				},
				{
					name: "PIN",
					sortable: true,
					selector: "pin",
				},
				{
					name: "Identificacion",
					sortable: true,
					selector: "identification",
				},
				{
					name: "Test ID",
					sortable: true,
					selector: "test_id",
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

	async fetchData() {
		await axios
			.get("api/software")
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
					toast.error(err.response.data.message);
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
				.get("api/software/" + event)
				.then((res) => {
					this.setState({
						titleModal: "Editar Datos",
						dataModal: res.data.data,
					});
				})
				.catch((err) => {
					toast.error(err.response.data.message);
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
			.get("api/software/delete/" + this.state.dataIdModalDelete)
			.then((res) => {
				this.closeModalDelete();
				toast.dismiss();
				toast.success(res.data.message);
			})
			.catch((err) => {
				toast.dismiss();
				toast.error(err.response.data.message);
			});
	}
	render() {
		return (
			<>
				<div className="card shadow mb-4">
					<div className="card-header py-3">
						<h6 className="m-0 font-weight-bold text-primary">
							Datos del software
						</h6>
					</div>
					<div className="card-body">
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
					</div>
				</div>
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
						<SoftwareForm
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
