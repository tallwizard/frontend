import React, { Component, Fragment } from 'react'
import * as yup from "yup";

const schemaValidate = yup.object().shape({
	items: yup.array()
		.of(
			yup.object().shape({
				productCode: yup.string("Debe ser de tipo texto")
					.required("Requerido")
					.max(50, "Maximo 50 caracteres"),
				productName: yup.string("Debe ser de tipo texto")
					.required("Requerido")
					.max(500, "Maximo 500 caracteres"),
				productBrand: yup.string("Debe ser de tipo texto").max(
					100,
					"Maximo 100 caracteres"
				),
				productAmount: yup.number()
					.typeError("Debe ser de tipo numerico")
					.required("Requerido")
					.min(1, "Debe contener una cantidad"),
				productPrice: yup.number()
					.typeError("Debe ser de tipo numerico")
					.required("Requerido")
					.min(1, "Debe contener un valor"),
				productDiscount: yup.number().typeError(
					"Debe ser de tipo numerico"
				),
				productReasonDiscount: yup.string("Debe ser de tipo texto")
					.max(200, "Maximo 200 caracteres")
					.when("productDiscount", (productDiscount) => {
						if (productDiscount > 0) {
							return yup.string("Debe ser de tipo texto")
								.max(200, "Maximo 200 caracteres")
								.required("Requerido");
						}
					}),
			}))
});

const initValues = {
	productCode: "",
	productName: "",
	productBrand: "",
	productAmount: '',
	productPrice: '',
	productDiscount: '',
	productReasonDiscount: "",
	productTotal: '',
	productTotalFormatter: 0,
}

export default class InvoiceDetail extends Component {
	constructor(props) {
		super(props)
		this.state = {
			items: [initValues],
			message: [initValues]
		}
	}

	async pushItem() {
		await this.setState({
			items: [...this.state.items, initValues]
		})
		this.props.result(this.state.items)
	}

	async removeItem(index) {
		await this.setState({
			items: this.state.items.filter(function (person, i) {
				return index !== i
			})
		});
		this.props.result(this.state.items)
	}

	clearData() {
		this.setState({ items: [initValues], message: [initValues] })
	}

	componentDidMount() {
		this.props.result(this.state.items)
	}

	async handleOnChange(index, value, inputName) {
		await this.changeState(index, value, inputName)
		await this.validateField(index, value, inputName)
		this.props.result(this.state.items)
	}

	changeState(index, value, inputName) {
		this.setState(({ items }) => ({
			items: [
				...items.slice(0, index),
				{
					...items[index],
					[inputName]: value,
				},
				...items.slice(index + 1)
			]
		}));
	}

	validateField(index, value, inputName) {
		schemaValidate.validateAt(`items[${index}].${inputName}`, this.state).then(res => {
			this.setState(({ message }) => ({
				message: [
					...message.slice(0, index),
					{
						...message[index],
						[inputName]: null,
					},
					...message.slice(index + 1)
				]
			}));
		}).catch((err) => {
			this.setState(({ message }) => ({
				message: [
					...message.slice(0, index),
					{
						...message[index],
						[inputName]: err.errors,
					},
					...message.slice(index + 1)
				]
			}));
		});
	}

	formatter(number) {
		return new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: 'USD',
		}).format(number)
	}

	validateAll() {
		return schemaValidate.validate(this.state, { abortEarly: false }).catch(err => {
			err.inner.forEach(element => {
				let index = element.path.match(/(\d+)/g)
				let path = (element.path.split('.'))[1]
				this.setState(({ message }) => ({
					message: [
						...message.slice(0, index),
						{
							...message[index],
							[path]: err.errors[0],
						},
						...message.slice(index + 1)
					]
				}));
			});
		})
	}

	render() {
		return (
			<>
				<div className="row d-flex justify-content-center">
					<div className="form-group col-12 py-1">
						<div
							onClick={this.pushItem.bind(this)}
							className="col-12 btn btn-outline-secondary"
						>
							<i className="fas fa-plus"></i>
							&nbsp;Agregar
						</div>
					</div>
				</div>
				{this.state.items.map((item, index) => {
					return (
						<Fragment key={index}>
							<fieldset
								className={"border rounded form-group p-2"}
							>
								<legend
									className={"col-form-label col-sm-2 pt-0"}
								>
									Item {index + 1}
								</legend>
								<div className="row">
									<div className=" col-10">
										<div className="row">
											<div className="form-group col-4">
												<label>Codigo</label>
												<input type="text" onChange={(e) => this.handleOnChange(index, e.target.value, 'productCode')} value={item.productCode} className="form-control" />
												<span className="text-danger">{this.state.message[index] ? this.state.message[index].productCode : null}</span>
											</div>
											<div className="form-group col-5">
												<label>Nombre</label>
												<input type="text" onChange={(e) => this.handleOnChange(index, e.target.value, 'productName')} value={item.productName} className="form-control" />
												<span className="text-danger">{this.state.message[index] ? this.state.message[index].productName : null}</span>
											</div>
											<div className="form-group col-3">
												<label>Marca</label>
												<input type="text" onChange={(e) => this.handleOnChange(index, e.target.value, 'productBrand')} value={item.productBrand} className="form-control" />
												<span className="text-danger">{this.state.message[index] ? this.state.message[index].productBrand : null}</span>
											</div>
										</div>
										<div className="row">
											<div className="form-group col-3">
												<label>Cantidad</label>
												<input type="number" onChange={(e) => this.handleOnChange(index, e.target.value, 'productAmount')} value={item.productAmount} className="form-control" />
												<span className="text-danger">{this.state.message[index] ? this.state.message[index].productAmount : null}</span>
											</div>
											<div className="form-group col-2">
												<label>Precio Unitario</label>
												<input type="number" onChange={(e) => this.handleOnChange(index, e.target.value, 'productPrice')} value={item.productPrice} className="form-control" />
												<span className="text-danger">{this.state.message[index] ? this.state.message[index].productPrice : null}</span>
											</div>
											<div className="form-group col-3">
												<label>Descuento</label>
												<input type="number" onChange={(e) => this.handleOnChange(index, e.target.value, 'productDiscount')} value={item.productDiscount} className="form-control" />
												<span className="text-danger">{this.state.message[index] ? this.state.message[index].productDiscount : null}</span>
											</div>
											<div className="form-group col-4">
												<label>Razon del descuento</label>
												<input type="text" onChange={(e) => this.handleOnChange(index, e.target.value, 'productReasonDiscount')} value={item.productReasonDiscount} className="form-control" />
												<span className="text-danger">{this.state.message[index] ? this.state.message[index].productReasonDiscount : null}</span>
											</div>
										</div>
									</div>
									<div className="col-2">
										<div className="form-group col-auto ">
											<label>Total</label>
											<input type="text" value={item.productTotalFormatter = this.formatter(item.productTotal = item.productPrice * item.productAmount - item.productDiscount)} className="form-control" disabled={true} />
										</div>
										<div className="col-auto">
											<label>&nbsp;</label>
											<div onClick={() => index !== 0 ? this.removeItem(index) : null} className={`btn btn-outline-danger form-control ${index === 0 ? 'disabled' : ''}`} >
												<i className="fas fa-trash"></i>
											</div>
										</div>
									</div>
								</div>
							</fieldset>
						</Fragment>
					)
				})}
			</>
		)
	}
}