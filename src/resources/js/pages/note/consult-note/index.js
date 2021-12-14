import React, { Component } from "react";
import { toast } from "react-toastify";
import DataTable from "react-data-table-component";
import { Spinner } from "react-bootstrap";
import datatableSpanish from "../../../components/static/datatable-locale.json";
import ExpandableInvoiceComponent from "../../../components/forms/ExpandableInvoiceComponent";
import moment from "moment";
import AutoComplete from "../../../components/Autocomplete";

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

export default class ConsultNote extends Component {
	constructor(props) {
		super(props);
		this.state = {
			data: [],
			disabled: true,
			loading: false,
			prefixInvoice: "",
			prefixData: [],
			consecutiveInvoice: "",
			consecutiveNote: "",
			client: "",
			dateStart: "",
			dateEnd: "",
			columns: [
				{
					name: "Prefijo",
					sortable: true,
					selector: "code",
					grow: 1,
				},
				{
					name: "Consecutivo",
					sortable: true,
					selector: "consecutive",
					grow: 1,
				},
				{
					name: "Descripcion",
					sortable: true,
					selector: "description",
					grow: 1,
				},
				{
					name: "Tipo",
					sortable: true,
					selector: "type_note",
					grow: 1,
				},
				{
					name: "Concepto",
					sortable: true,
					selector: "concept",
					grow: 1,
				},
				{
					name: "Total",
					sortable: true,
					selector: "total",
					grow: 1,
				},
				{
					name: "Factura",
					sortable: true,
					selector: "invoice",
					grow: 1,
				},
				{
					name: "Estado",
					sortable: true,
					selector: "status",
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

	componentDidMount() {
		axios.get('api/invoices/prefix').then((res) => {
			this.setState({ prefixData: res.data.data })
		}).catch((err) => {
			console.log('error', err)
		})
	}

	download(type, url) {
		window.open(process.env.MIX_API + "/" + url + "." + type);
	}

	async export(type) {
		toast.dismiss();
		toast.info("Exportando...", { autoClose: false });
		await axios
			.post(
				"api/export/note",
				{
					prefixInvoice: this.state.prefixInvoice,
					consecutiveInvoice: this.state.consecutiveInvoice,
					consecutiveNote: this.state.consecutiveNote,
					client: this.state.client,
					dateStart: this.state.dateStart,
					dateEnd: this.state.dateEnd,
					type: type,
				},
				{ responseType: "blob" }
			)
			.then((res) => {
				console.log(res);
				toast.dismiss();
				toast.success("Notas Exportadas");
				const blob_file = res.data;
				const file_url = URL.createObjectURL(blob_file);
				window.open(file_url); // open file in new tab
			})
			.catch((err) => {
				console.log(err);
				toast.dismiss();
				toast.error("Error del servidor");
			});
	}

	handleAutocomplete(event) {
		this.setState({ client: event });
	}

	async consult() {
		toast.dismiss();
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
		toast.info("Consultando...", { autoClose: false });
		this.setState({ loading: true });
		await axios
			.post("api/notes/consult", {
				prefixInvoice: this.state.prefixInvoice,
				consecutiveInvoice: this.state.consecutiveInvoice,
				consecutiveNote: this.state.consecutiveNote,
				client: this.state.client,
				dateStart: this.state.dateStart,
				dateEnd: this.state.dateEnd,
			})
			.then((res) => {
				toast.dismiss();
				if (res.status == 200) {
					this.setState({
						data: res.data.data,
						loading: false,
						disabled: false,
					});
				}
			})
			.catch((err) => {
				toast.dismiss();
				toast.error(err.response.data.message, {
					autoClose: false,
				});
				this.setState({ data: [], loading: false, disabled: true });
			});
	}

	render() {
		return (
			<>
				<div className="my-5 mx-5">
					<div className="row">
						<div className="form-group col-2">
							<label htmlFor="">Prefijo Factura</label>
							<select
								className="form-control"
								id="prefix"
								name="prefix"
								value={this.state.prefixInvoice}
								onChange={(e) =>
									this.setState({
										prefixInvoice: e.target.value,
									})
								}
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
							<label htmlFor="">Consecutivo Factura</label>
							<input
								type="text"
								className="form-control"
								onChange={(e) =>
									this.setState({
										consecutiveInvoice: e.target.value,
									})
								}
							/>
						</div>
						<div className="form-group col-2">
							<label htmlFor="">Consecutivo Nota</label>
							<input
								type="text"
								className="form-control"
								onChange={(e) =>
									this.setState({
										consecutiveNote: e.target.value,
									})
								}
							/>
						</div>
						<div className="form-group col-2">
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
								type="date"
								className="form-control"
								onChange={(e) =>
									this.setState({
										dateStart: e.target.value,
									})
								}
							/>
						</div>
						<div className="form-group col-2">
							<label htmlFor="">Fecha Final</label>
							<input
								type="date"
								className="form-control"
								onChange={(e) =>
									this.setState({
										dateEnd: e.target.value,
									})
								}
							/>
						</div>
					</div>
					<div className="form-group col-12  d-flex justify-content-center">
						<label htmlFor="">&nbsp;&nbsp;</label>
						<br />
						<button
							className="btn btn-success col-md-2"
							onClick={() => this.consult()}
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
			</>
		);
	}
}
