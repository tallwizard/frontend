import React, { Component } from "react";
import { toast } from "react-toastify";
import DataTable from "react-data-table-component";
import { Modal, Spinner } from "react-bootstrap";
import datatableSpanish from "../../components/static/datatable-locale.json";
import ModalDialog from "../../components/ModalDialog";
import ResolutionForm from "../../components/forms/ResolutionForm";

export default class Resolution extends Component {
	constructor(props) {
		super(props);
		this.state = {
			data: [],
			dependenceData: [],
			loading: true,
			showModal: false,
			showModalDelete: false,
			titleModal: "Añadir Datos",
			dataModal: [],
			dataModalDelete: {
				title: "Eliminar Dato",
				body: "¿Esta seguro que desea eliminar este elemento?",
			},
			columns: [
				{
					name: "Codigo",
					sortable: true,
					selector: "code",
				},
				{
					name: "Numero",
					sortable: true,
					selector: "number",
				},
				{
					name: "Llave",
					sortable: true,
					selector: "key",
				},
				{
					name: "Fecha inicial",
					sortable: true,
					selector: "start_date",
				},
				{
					name: "Fecha final",
					sortable: true,
					selector: "end_date",
				},
				{
					name: "Consecutivo inicial",
					sortable: true,
					selector: "start_consecutive",
				},
				{
					name: "Consecutivo final",
					sortable: true,
					selector: "end_consecutive",
				},
				{
					name: "Prefijo de factura",
					sortable: true,
					selector: "prefix",
				},
				{
					name: "Dependencia",
					sortable: true,
					selector: "dependence",
				},
				{
					name: "Estado",
					sortable: true,
					selector: "active",
					cell: (row) => {
						if (row.active == true) {
							return <div>Activo</div>;
						} else
							return <div>Inactivo</div>;
					}
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
			.get("api/resolution")
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
		await axios
			.get("api/dependence")
			.then((res) => {
				if (res.status == 200) {
					this.setState({
						dependenceData: res.data.data,
					});
				}
			})
			.catch((err) => {
				this.setState({
					dependenceData: [],
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
				.get("api/resolution/" + event)
				.then((res) => {
					toast.dismiss();
					this.setState({
						titleModal: "Editar Datos",
						dataModal: res.data.data,
					});
				})
				.catch((err) => {
					toast.dismiss();
					toast.error(err.response.data.message);
				});
		} else {
			toast.dismiss();
			this.setState({
				titleModal: "Añadir Datos",
				dataModal: [],
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
			.get("api/resolution/delete/" + this.state.dataIdModalDelete)
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
							Resoluciones
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
						<ResolutionForm
							dependenceData={this.state.dependenceData}
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
