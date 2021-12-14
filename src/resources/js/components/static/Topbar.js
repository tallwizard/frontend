import React, { useEffect, useState } from "react";
import avatar from "../../../img/no-avatar.png";
import { Modal } from "react-bootstrap";
import { useHistory } from "react-router-dom";
import UserForm from "../forms/UserForm";
import { toast } from "react-toastify";

function Topbar() {
	const user = JSON.parse(localStorage.getItem("user"));
	const [show, setShow] = useState(false);
	const closeModal = () => setShow(false);
	const history = useHistory();

	async function logout() {
		toast.dismiss();
		toast.info("Cerrando sesion...", { autoClose: false });
		await axios
			.post("api/logout")
			.then((res) => {
				toast.dismiss();
				localStorage.clear();
				toast.success(res.data.message);
				history.push("/login")
			})
			.catch((err) => {
				toast.dismiss();
				if (err.response.message) {
					toast.error(err.response.message);
				} else {
					localStorage.clear();
					toast.error("Error del servidor");
					history.push("/login")
				}
			});
	}

	return (
		<>
			<nav className="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
				<button
					id="sidebarToggleTop"
					className="btn btn-link d-md-none rounded-circle mr-3"
				>
					<i className="fa fa-bars"></i>
				</button>
				<ul className="navbar-nav ml-auto">
					<li className="nav-item dropdown no-arrow">
						<a
							className="nav-link dropdown-toggle"
							href="#"
							id="userDropdown"
							role="button"
							data-toggle="dropdown"
							aria-haspopup="true"
							aria-expanded="false"
						>
							<span className="mr-2 d-none d-lg-inline text-gray-600 small">
								{user.name + " | " + user.role}
							</span>
							<div className="topbar-divider d-none d-sm-block" />
							<img
								className="img-profile rounded-circle"
								src={avatar}
							/>
						</a>
						<div
							className="dropdown-menu dropdown-menu-right shadow animated--grow-in"
							aria-labelledby="userDropdown"
						>
							<span
								className="dropdown-item"
								onClick={() => setShow(true)}
							>
								<i className="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
								Informaci&oacute;n Personal
							</span>
							<div className="dropdown-divider" />
							<a onClick={logout} className="dropdown-item">
								<i className="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
								Cerrar Sesi&oacute;n
							</a>
						</div>
					</li>
				</ul>
			</nav>
			<Modal
				scrollable={true}
				size="md"
				show={show}
				onHide={() => closeModal()}
				keyboard={false}
			>
				<Modal.Header closeButton>
					<Modal.Title>Actualizar Datos</Modal.Title>
				</Modal.Header>
				<Modal.Body>
					<UserForm
						dataModal={user}
						closeModal={() => closeModal()}
					/>
				</Modal.Body>
			</Modal>
		</>
	);
}

export default Topbar;