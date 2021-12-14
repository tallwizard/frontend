import React, { Component } from "react";
import InvoiceDetail from "./InvoiceDetail";
import InvoiceHeader from "./InvoiceHeader";
import { toast } from 'react-toastify'

export default class CreateInvoice extends Component {
	constructor(props) {
		super(props);
		this.state = {
			validateHeader: false,
			form: {}
		};
		this.total = 0
		this.resultHeader = this.resultHeader.bind(this)
		this.resultDetail = this.resultDetail.bind(this)
		this.refInvoiceHeader = React.createRef();
		this.refInvoiceDetail = React.createRef();
	}

	resultHeader(event) {
		this.setState((prev) => {
			return {
				...prev,
				form: { ...prev.form, header: event },
			};
		});
	}

	resultDetail(event) {
		this.total = event.reduce((accumulator, currentValue) => parseFloat(accumulator + currentValue.productTotal), 0)
		this.setState((prev) => {
			return {
				...prev,
				form: { ...prev.form, items: event },
			};
		});
	}

	async handleOnSubmit() {
		toast.dismiss()
		toast.info('Guardando...', { autoClose: false })
		const resultDetail = await this.refInvoiceDetail.current.validateAll()
		const resultHeader = await this.refInvoiceHeader.current.validateAll()
		if (!resultHeader || !resultDetail) {
			toast.dismiss()
			toast.error('Se detectaron errores en el formulario')
		} else {
			if (this.state.form.header.total < 0) {
				toast.dismiss()
				toast.error('El valor total de la factura no puede ser negativo')
				return
			}
			axios.post("api/invoices/create", this.state.form)
				.then((res) => {
					toast.dismiss();
					toast.success(
						"Factura " + res.data.data + " creada correctamente",
						{
							autoClose: false,
						}
					);
					this.refInvoiceHeader.current.clearData()
					this.refInvoiceDetail.current.clearData()
				})
				.catch((err) => {
					toast.dismiss();
					if (err.response.message) {
						toast.error(err.response.message)
					} else {
						let obj = Object.values(err.response.data.errors);
						obj.map((e) => {
							toast.error(e[0]);
						});
					}
				});
		}
	}

	render() {
		return (
			<div className="my-5 mx-5">
				<h4>Encabezado</h4>
				<hr />
				<InvoiceHeader result={this.resultHeader} total={this.total} ref={this.refInvoiceHeader} />
				<div className="mt-3">
					<h4>Detalle</h4>
				</div>
				<hr />
				<InvoiceDetail result={this.resultDetail} ref={this.refInvoiceDetail} />

				<button onClick={this.handleOnSubmit.bind(this)} className="btn btn-primary btn-user btn-block my-2">
					Enviar
				</button>
			</div>
		);
	}
}
