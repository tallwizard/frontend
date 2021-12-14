import React, { Component } from "react";
import AutoComplete from "../../../components/Autocomplete";
import * as yup from "yup";

const schemaValidate = yup.object().shape({
	prefix: yup.string().required("Requerido"),
	client: yup.string("Debe ser de tipo numerico").required("Requerido"),
	expirationDate: yup.date().typeError('Debe ser de tipo fecha').min(
		(new Date().toISOString().split("T")[0]),
		"Fecha minima debe ser " + (new Date().toISOString().split("T")[0])
	),
	description: yup.string("Debe ser de tipo texto")
		.required("Requerido")
		.max(1000, "Maximo 1000 caracteres"),
	wayPay: yup.string().required("Requerido"),
	payMethod: yup.string().required("Requerido"),
	bankAccount: yup.string("Debe ser de tipo texto").max(
		200,
		"Maximo 200 caracteres"
	),

});

const initValues = {
	prefix: "",
	client: "",
	expirationDate: new Date().toISOString().split("T")[0],
	description: "",
	wayPay: "",
	payMethod: "",
	bankAccount: "",
	total: ''
}

export default class InvoiceHeader extends Component {
	constructor(props) {
		super(props);
		this.state = {
			prefix: [],
			wayPay: [],
			payMethod: [],
			form: initValues,
			message: {},
		};
	}

	clearData() {
		this.setState({ form: initValues, message: {} })
	}

	async fetchData() {
		await axios
			.get("api/invoices/prefix", {
				cancelToken: this.source.token
			})
			.then((res) => {
				this.setState({ prefix: res.data.data });
			}).catch(err => {
				console.log(err)
			});
		await axios
			.get("api/invoices/waypay", {
				cancelToken: this.source.token
			})
			.then((res) => {
				this.setState({ wayPay: res.data.data });
			}).catch(err => {
				console.log(err)
			});
		await axios
			.get("api/invoices/paymethod", {
				cancelToken: this.source.token
			})
			.then((res) => {
				this.setState({ payMethod: res.data.data });
			}).catch(err => {
				console.log(err)
			});
	}

	componentDidMount() {
		const CancelToken = axios.CancelToken;
		this.source = CancelToken.source();
		this.fetchData();
		this.props.result(this.state.form)
	}

	componentWillUnmount() {
		this.source.cancel('Cancelado')
	}

	async handleOnChange(e, arg = false) {
		let name, value
		if (arg) {
			name = 'client'
			value = e
		} else {
			name = e.target.name
			value = e.target.value
		}
		await this.changeState(name, value)
		await this.validateField(name, value)
		this.props.result(this.state.form)

	}

	validateField(name, value) {
		schemaValidate.validateAt(name, { [name]: value }).then(res => {
			this.setState((prev) => {
				return {
					...prev,
					message: { ...prev.message, [name]: null },
				};
			});
		}).catch((err) => {
			this.setState((prev) => {
				return {
					...prev,
					message: { ...prev.message, [name]: err.errors },
				};
			});
		});
	}

	validateAll() {
		return schemaValidate.validate(this.state.form, { abortEarly: false }).catch(err => {
			err.inner.forEach(element => {
				this.setState((prev) => {
					return {
						...prev,
						message: { ...prev.message, [element.path]: element.message },
					};
				});
			});
		})
	}

	changeState(name, value) {
		this.setState((prev) => {
			return {
				...prev,
				form: { ...prev.form, [name]: value },
			};
		});
	}

	formatter(number) {
		return new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: 'USD',
		}).format(number)
	}

	render() {
		return (
			<>
				<div className="row">
					<div className="form-group col-4">
						<label htmlFor="prefix">Prefijo</label>
						<select
							className="form-control"
							id="prefix"
							name="prefix"
							value={this.state.form.prefix}
							onChange={this.handleOnChange.bind(this)}
						>
							<option value="">Seleccione...</option>
							{this.state.prefix.map((option) => {
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
						<span ref="prefix" className="text-danger">{this.state.message.prefix}</span>
					</div>
					<div className="form-group col-5">
						<label htmlFor="client">Tercero</label>
						<AutoComplete
							multiple={false}
							results={this.handleOnChange.bind(this)}
							urlSearch="api/clients/autocomplete/"
						/>
						<span className="text-danger">{this.state.message.client}</span>
					</div>
					<div className="form-group col-3">
						<label htmlFor="expirationDate">
							Fecha de Vencimiento
						</label>
						<input
							type="date"
							className="form-control"
							name="expirationDate"
							value={this.state.form.expirationDate}
							onChange={this.handleOnChange.bind(this)}
							min={new Date().toISOString().split("T")[0]}
						/>
						<span className="text-danger">{this.state.message.expirationDate}</span>
					</div>
				</div>
				<div className="row">
					<div className="form-group col-12">
						<label htmlFor="description">Descripci&oacute;n</label>
						<textarea
							className="form-control"
							name="description"
							id="description"
							value={this.state.form.description}
							onChange={this.handleOnChange.bind(this)}
							rows="3"
						></textarea>
						<span className="text-danger">{this.state.message.description}</span>
					</div>
				</div>
				<div className="row">
					<div className="form-group col-2">
						<label htmlFor="wayPay">Forma de pago</label>
						<select
							className="form-control"
							name="wayPay"
							id="wayPay"
							value={this.state.form.wayPay}
							onChange={this.handleOnChange.bind(this)}
						>
							<option value="">Seleccione...</option>
							{this.state.wayPay.map((option) => {
								return (
									<option key={option.id} value={option.id}>
										{option.name}
									</option>
								);
							})}
						</select>
						<span className="text-danger">{this.state.message.wayPay}</span>
					</div>
					<div className="form-group col-4">
						<label htmlFor="payMethod">Medio de pago</label>
						<select
							className="form-control"
							name="payMethod"
							id="payMethod"
							value={this.state.form.payMethod}
							onChange={this.handleOnChange.bind(this)}
						>
							<option value="">Seleccione...</option>
							{this.state.payMethod.map((option) => {
								return (
									<option key={option.id} value={option.id}>
										{option.name}
									</option>
								);
							})}
						</select>
						<span className="text-danger">{this.state.message.payMethod}</span>
					</div>
					<div className="form-group col-4">
						<label htmlFor="bankAccount">Cuenta Bancaria</label>
						<input
							type="text"
							className="form-control"
							name="bankAccount"
							id="bankAccount"
							value={this.state.form.bankAccount}
							onChange={this.handleOnChange.bind(this)}
							placeholder="Banco 0000000000"
						/>

						<span className="text-danger">{this.state.message.bankAccount}</span>

					</div>
					<div className="form-group col-2">
						<label htmlFor="total">Total</label>
						<input
							readOnly
							type="text"
							className="form-control"
							name="total"
							id="total"
							value={this.formatter(this.state.form.total = this.props.total)}
						/>
					</div>
				</div>
			</>
		);
	}
}
