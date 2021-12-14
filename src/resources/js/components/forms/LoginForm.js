import React, { Component } from "react";
import * as Yup from "yup";

const schemaValidate = Yup.object().shape({
	email: Yup.string("Texto invalido")
		.min(5, "M\u00EDnimo 5 caracteres")
		.max(100, "M\u00E1ximo 100 caracteres")
		.email("Correo invalido")
		.required("Requerido"),
	password: Yup.string("Texto invalido")
		.min(5, "M\u00EDnimo 5 caracteres")
		.max(20, "M\u00E1ximo 20 caracteres")
		.required("Requerido"),
});


export default class LoginForm extends Component {
	constructor(props) {
		super(props)
		this.state = {
			form: {
				email: '',
				password: ''
			},
			message: {}
		}
	}

	changeState(name, value) {
		this.setState((prev) => {
			return {
				...prev,
				form: { ...prev.form, [name]: value },
			};
		});
	}

	handleOnChange(e) {
		let name = e.target.name
		let value = e.target.value
		this.changeState(name, value)
		this.validateField(name, value)
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

	handleOnSubmit() {
		schemaValidate.validate(this.state.form, { abortEarly: false }).then(res => {
			this.props.result(res)
		}).catch(err => {
			err.inner.forEach(e => {
				this.setState((prev) => {
					return {
						...prev,
						message: { ...prev.message, [e.path]: e.message },
					};
				});
			});
		})
	}

	render() {
		return (
			<>
				<div className="form-group">
					<label htmlFor="email">Correo</label>
					<input
						type="text"
						className="form-control"
						name="email"
						id="email"
						value={this.state.form.email}
						onKeyDown={(e) => e.key === 'Enter' ? this.handleOnSubmit() : null}
						onChange={this.handleOnChange.bind(this)}

					/>
					<span className="text-danger">{this.state.message.email}</span>
				</div>
				<div className="form-group">
					<label htmlFor="email">Contrase&ntilde;a</label>
					<input
						type="password"
						className="form-control"
						name="password"
						id="password"
						value={this.state.form.password}
						onKeyDown={(e) => e.key === 'Enter' ? this.handleOnSubmit() : null}
						onChange={this.handleOnChange.bind(this)}
					/>
					<span className="text-danger">{this.state.message.password}</span>
				</div>
				<hr />
				<button
					type="submit"
					onClick={this.handleOnSubmit.bind(this)}
					className="btn btn-primary btn-user btn-block my-2"
				>
					Ingresar
				</button>
			</>
		)
	}
}

