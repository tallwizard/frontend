import React, { Component } from "react";
import styles from "./style.module.css";
import AOS from "aos";
import "aos/dist/aos.css";
import { Link } from "react-router-dom";
import { toast } from "react-toastify";

export default class Welcome extends Component {
	constructor(props) {
		super(props)
		AOS.init();
		AOS.refresh();
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

	render() {
		return (
			<div className={styles.container}>
				<div className={styles.wrapper}>
					<div className={styles.content}>
						<div data-aos="zoom">
							<h1>Bienvenido</h1>
							<h3>
								Portal del proveedor tecnológico de Sinergia S.A.S
							</h3>
							<Link to="/login" className="btn btn-primary">
								Ingresar
							</Link>
						</div>
					</div>
				</div>
			</div>
		)
	}
}
