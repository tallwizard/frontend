import React, { Component } from "react";
import { toast } from "react-toastify";
import DataTable from "react-data-table-component";
import { Spinner } from "react-bootstrap";
import datatableSpanish from "../../../components/static/datatable-locale.json";
import ExpandableInvoiceComponent from "../../../components/forms/ExpandableInvoiceComponent";
import AutoComplete from "../../../components/Autocomplete";
import axios from "axios";
import moment from "moment";

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

export default class ConsultInvoice extends Component {
	constructor(props) {
		super(props);
		this.state = {
			data: [],
			disabled: true,
			dataDetail: [],
			loading: false,
			prefix: "",
			prefixData: [],
			consecutive: "",
			client: "",
			dateStart: "",
			dateEnd: "",
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
								disabled={row.url == null ? true : false}
								className="btn btn-secondary col-6"
								onClick={() => this.download("pdf", row.url)}
							>
								<i className="fas fa-file-pdf"></i>
							</button>
							<button
								disabled={row.url == null ? true : false}
								className="btn btn-primary col-6"
								onClick={() => this.download("xml", row.url)}
							>
								<i className="fas fa-file-excel"></i>
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

	download(type, url) {
		window.open(process.env.MIX_API + url + "." + type);
	}

	async consult() {
		toast.info("Consultando...", { autoClose: false });
		if (this.state.dateEnd && this.state.dateStart) {
			if (this.state.dateEnd < this.state.dateStart) {
				toast.dismiss();
				return toast.error(
					"Las fecha final debe ser mayor a: " +
					moment(this.state.dateStart).format("DD/MM/YYYY"),
					{
						autoClose: false,
					}
				);
			}
		}
		this.setState({ loading: true });
		await axios
			.post("api/invoices/consult", {
				prefix: this.state.prefix,
				consecutive: this.state.consecutive,
				client: this.state.client,
				dateStart: this.state.dateStart,
				dateEnd: this.state.dateEnd,
			})
			.then((res) => {
				if (res.status == 200) {
					toast.dismiss();
					this.setState({
						data: res.data.data,
						loading: false,
						disabled: false,
					});
				}
			})
			.catch((err) => {
				let autoClose = true;
				if (err.status == 500) {
					autoClose = false;
				}
				toast.dismiss();
				toast.error(err.response.data.message, {
					autoClose: autoClose,
				});
				this.setState({ data: [], loading: false, disabled: true });
			});
	}

	async export(type) {
		toast.dismiss();
		toast.info("Exportando...", { autoClose: false });
		await axios
			.post(
				"api/export/invoice",
				{
					type: type,
					prefix: this.state.prefix,
					consecutive: this.state.consecutive,
					client: this.state.client,
					dateStart: this.state.dateStart,
					dateEnd: this.state.dateEnd,
				},
				{ responseType: "blob" }
			)
			.then((res) => {
				toast.dismiss();
				toast.success("Facturas Exportadas");
				const blob_file = res.data;
				const file_url = URL.createObjectURL(blob_file);
				window.open(file_url); // open file in new tab
			})
			.catch((err) => {
				toast.dismiss();
				toast.error("Error del servidor");
			});
	}

	handleAutocomplete(event) {
		this.setState({ client: event });
	}

	handleOnChange(e, arg = false) {
		let name, value
		if (arg) {
			name = 'client'
			value = e
		} else {
			name = e.target.name
			value = e.target.value
		}
		this.setState((prev) => {
			return {
				...prev,
				[name]: value,
			};
		});
	}
	componentDidMount() {
		axios.get('api/invoices/prefix').then((res) => {
			this.setState({ prefixData: res.data.data })
		}).catch((err) => {
			console.log('error', err)
		})
	}

	render() {
		return (
			<>
				<div className="my-5 mx-5">
					<div className="row">
						<div className="form-group col-3">
							<label htmlFor="">Prefijo</label>
							<select
								className="form-control"
								id="prefix"
								name="prefix"
								value={this.state.prefix}
								onChange={this.handleOnChange.bind(this)}
							>
								<option value="">Seleccione...</option>
								{this.state.prefixData.map((option) => {
									return (
										<option
											key={option.code}
											value={option.code}
										>
											{option.code}
										</option>
									);
								})}
							</select>
						</div>
						<div className="form-group col-2">
							<label htmlFor="">Consecutivo</label>
							<input
								name="consecutive"
								type="text"
								className="form-control"
								onChange={this.handleOnChange.bind(this)}
							/>
						</div>
						<div className="form-group col-3">
							<label htmlFor="">Tercero</label>
							<AutoComplete
								multiple={true}
								results={this.handleAutocomplete.bind(this)}
								urlSearch="api/clients/autocomplete/"
							/>
						</div>
						<div className="form-group col-2">
							<label htmlFor="">Fecha Inicial</label>
							<input
								name="dateStart"
								type="date"
								className="form-control"
								onChange={this.handleOnChange.bind(this)}
							/>
						</div>
						<div className="form-group col-2">
							<label htmlFor="">Fecha Final</label>
							<input
								name="dateEnd"
								type="date"
								className="form-control"
								onChange={this.handleOnChange.bind(this)}
							/>
						</div>
					</div>
					{/* offset-2 */}
					<div className="form-group col-12  d-flex justify-content-center">
						<label htmlFor="">&nbsp;&nbsp;</label>
						<br />
						<button
							className="btn btn-success col-md-2"
							onClick={this.consult.bind(this)}
						>
							<i className="fas fa-search mr-2 my-auto" />
							Buscar
						</button>
						<button
							disabled={this.state.disabled}
							className="btn btn-success col-md-2 mx-2"
							onClick={() => this.export("pdf")}
						>
							<i className="fas fa-search mr-2 my-auto" />
							Exportar PDF
						</button>
						<button
							disabled={this.state.disabled}
							className="btn btn-success col-md-2"
							onClick={() => this.export("excel")}
						>
							<i className="fas fa-search mr-2 my-auto" />
							Exportar CSV
						</button>
					</div>
				</div>
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
					expandableRows
					expandableRowsComponent={
						<ExpandableInvoiceComponent import={false} />
					}
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
					columns={this.state.columns}
					data={this.state.data}
					paginationComponentOptions={datatableSpanish}
				/>
			</>
		);
	}
}
