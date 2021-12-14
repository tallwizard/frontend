import React, { Component } from "react";
import { toast } from "react-toastify";
import DataTable from "react-data-table-component";
import { Modal, Spinner } from "react-bootstrap";
import datatableSpanish from "../../../components/static/datatable-locale.json";
import moment from "moment";
import ModalDialog from "../../../components/ModalDialog";
moment.locale("es-us");

const conditionalRowStyles = [
	{
		when: (row) => row.status == 1,
		style: {
			backgroundColor: "rgba(248, 148, 6, 0.9)",
			color: "white",
		},
	},
	{
		when: (row) => row.status == 2,
		style: {
			backgroundColor: "rgba(63, 195, 128, 0.9)",
			color: "white",
		},
	},
	{
		when: (row) => row.status == 3,
		style: {
			backgroundColor: "rgba(242, 38, 19, 0.9)",
			color: "white",
		},
	},
];

export default class PendingInvoice extends Component {
	constructor(props) {
		super(props);
		this.state = {
			data: [],
			loading: true,
			showModal: false,
			showModalDelete: false,
			titleModal: "Añadir Datos",
			dataModalDelete: {
				title: "Eliminar Dato",
				body: "¿Esta seguro que desea eliminar este elemento?",
			},
			columns: [
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
				{
					name: "Estado",
					sortable: true,
					selector: "dian_status",
					cell: (row) => {
						if (row.status == 1) {
							return <div>Pendiente</div>;
						} else if (row.status == 2) {
							return <div>Aceptado</div>;
						} else if (row.status == 3) {
							return <div>Rechazado</div>;
						}
					},
					grow: 1,
				},
				{
					name: "Acciones",
					cell: (row) => (
						<div className="row col-12">
							<button
								disabled={row.status != 3 ? true : false}
								className="btn btn-danger mx-auto"
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
		await axios.get("api/invoices/loading", {
			cancelToken: this.source.token
		})
			.then((res) => {
				if (res.status == 200) {
					this.setState({
						data: res.data.data,
						loading: false,
					});
				}
			})
			.catch((err) => {
				if (err.status == 500) {
					toast.dismiss();
					toast.error(err.response.data.message, {
						autoClose: false,
					});
				}
				this.setState({ data: [], loading: false });
			});
	}

	componentDidMount() {
		const CancelToken = axios.CancelToken;
		this.source = CancelToken.source();
		this.currentInterval = setInterval(() => {
			this.fetchData();
		}, 5000);
	}

	componentWillUnmount() {
		this.source.cancel('Cancelado')
		clearInterval(this.currentInterval);

	}

	openModalDelete(event) {
		this.setState({
			showModalDelete: true,
			dataIdModalDelete: event,
		});
	}

	closeModalDelete() {
		this.fetchData();
		this.setState({ showModalDelete: false });
	}


	async acceptModalDelete() {
		await axios
			.get("api/invoices/delete/" + this.state.dataIdModalDelete,{
			cancelToken: this.source.token
		})
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
					className="my-5"
					fixedHeader={true}
					fixedHeaderScrollHeight="50vh"
					highlightOnHover
					conditionalRowStyles={conditionalRowStyles}
					noHeader
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
