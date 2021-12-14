

import React, { Component } from "react";
import { toast } from "react-toastify";
import * as Yup from "yup";

const schemaValidate = Yup.object().shape({
	name: Yup.string("Texto invalido")
		.min(3, "M\u00EDnimo 3 caracteres")
		.max(60, "M\u00E1ximo 60 caracteres")
		.required("Requerido"),
	email: Yup.string("Texto invalido")
		.max(100, "M\u00E1ximo 100 caracteres")
		.email("Correo invalido")
		.required("Requerido"),
	password: Yup.string("Texto invalido")
		.max(20, "M\u00E1ximo 20 caracteres"),
	role: Yup.string().required("Requerido"),
	active: Yup.boolean()
});

export default class UserForm extends Component {
	constructor(props) {
		super(props)
		this.state = {
			roles: [],
			form: {
				id: this.props.dataModal.id ? this.props.dataModal.id : '',
				name: this.props.dataModal.name ? this.props.dataModal.name : '',
				email: this.props.dataModal.email ? this.props.dataModal.email : '',
				password: '',
				role: this.props.dataModal.roles_id ? this.props.dataModal.roles_id : '',
				active: this.props.dataModal.active ? true : false
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

	async fetchData() {
		await axios.get('api/user/roles').then(res => {
			this.setState({ roles: res.data });
		})
	}

	componentDidMount() {
		this.fetchData()
	}

	handleOnSubmit() {
		schemaValidate.validate(this.state.form, { abortEarly: false }).then(values => {
			console.log(values)
			if (values.id) {
				axios
					.post("api/user/update", values)
					.then((res) => {
						toast.success(res.data.message);
						this.props.closeModal();
					})
					.catch((err) => {
						toast.dismiss();
						if (err.response.data.errors) {
							let errors = err.response.data.errors;
							errors.forEach((element) => {
								toast.error(element);
							});
						} else {
							toast.error(err.response.data.message)
						}
					});
			} else {
				axios
					.post("api/user/create", values)
					.then((res) => {
						toast.success(res.data.message);
						this.props.closeModal();
					})
					.catch((err) => {
						toast.dismiss();
						if (err.response.data.errors) {
							let errors = err.response.data.errors;
							errors.forEach((element) => {
								toast.error(element);
							});
						} else {
							toast.error(err.response.data.message)
						}
					});
			}
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
			<div className="mx-5">
				<div className="form-group">
					<label>Name</label>
					<input
						type="text"
						className="form-control"
						name="name"
						id="name"
						value={this.state.form.name}
						onChange={this.handleOnChange.bind(this)}

					/>
					<span className="text-danger">{this.state.message.name}</span>
				</div>
				<div className="form-group">
					<label>Correo</label>
					<input
						type="text"
						className="form-control"
						name="email"
						value={this.state.form.email}
						onChange={this.handleOnChange.bind(this)}
					/>
					<span className="text-danger">{this.state.message.email}</span>
				</div>
				<div className="form-group">
					<label>Contrase&ntilde;a</label>
					<input
						type="password"
						className="form-control"
						name="password"
						value={this.state.form.password}
						onChange={this.handleOnChange.bind(this)}
					/>
					<span className="text-danger">{this.state.message.password}</span>
				</div>

				<div className="form-group">
					<label>Rol</label>
					<select
						className="form-control"
						name="role"
						onChange={this.handleOnChange.bind(this)}
						value={this.state.form.role}
					>
						<option value={null}>Seleccione...</option>
						{this.state.roles.map((option) => {
							return (
								<option
									key={option.id}
									value={option.id}
								>
									{option.name}
								</option>
							);
						})}
					</select>
					<span className="text-danger">{this.state.message.role}</span>
				</div>
				<div className="form-group">
					<label>Estado</label>
					<select
						className="form-control"
						name="active"
						onChange={this.handleOnChange.bind(this)}
						value={this.state.form.active}
					>
						<option value={true}>Activo</option>
						<option value={false}>Inactivo</option>
					</select>
					<span className="text-danger">{this.state.message.active}</span>
				</div>
				<hr />
				<button
					type="submit"
					onClick={this.handleOnSubmit.bind(this)}
					className="btn btn-primary btn-user btn-block my-2"
				>
					{this.state.form.id ? 'Actualizar' : 'Guardar'}
				</button>
			</div >
		)
	}
}


