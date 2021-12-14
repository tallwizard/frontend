import React, { Component } from "react";
import styles from "./style.module.css";
import LoginForm from "../../components/forms/LoginForm";
import imgLogin from "../../../img/login.png";
import { toast } from "react-toastify";

export default class Login extends Component {
	constructor(props) {
		super(props)
	}

	componentDidMount() {
		if (localStorage.getItem('token')) {
			axios.post('api/validate/token').then(res => {
				let user = JSON.parse(localStorage.getItem("user"))
				toast.success("Bienvenido. " + user.name);
				this.props.history.push('/invoice')
			})
		}
	}

	async result(state) {
		toast.dismiss();
		toast.info("Validando...", { autoClose: false });
		await axios.post('api/login', state).then(res => {
			toast.dismiss();
			localStorage.setItem(
				"user",
				JSON.stringify(res.data.user)
			);
			localStorage.setItem(
				"token",
				res.data.access_token
			);
			toast.success("Bienvenido. " + res.data.user.name);
			this.props.history.push('/invoice')
		}).catch(err => {
			toast.dismiss();
			if (err.response.data.errors) {
				let errors = err.response.data.errors;
				errors.forEach((element) => {
					toast.error(element);
				});
			} else {
				toast.error(err.response.data.message)
			}
		})
	}

	render() {
		return (
			<div className={styles.container}>
				<div className="container">
					<div className="row justify-content-center">
						<div className="col-xl-10 col-lg-12 col-md-9">
							<div className=" card o-hidden border-0 shadow-lg my-5">
								<div className="card-body p-0">
									<div className="row">
										<div className="col-lg-6 d-none d-lg-block mx-auto my-auto">
											<img
												src={imgLogin}
												className="col-12 "
											/>
										</div>
										<div className="col-lg-6">
											<div className="p-5">
												<div className="text-center">
													<h1 className="h4 text-gray-900 mb-5">
														Bienvenido
													</h1>
												</div>
												<LoginForm result={this.result.bind(this)} />
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		);
	}
}
