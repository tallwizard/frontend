import React from "react";
import { Link } from "react-router-dom";

const SidebarItems = () => {
	const user = JSON.parse(localStorage.getItem("user"));
	return (
		<>
			<li className="nav-item">
				<a
					className="nav-link collapsed"
					href="#"
					data-toggle="collapse"
					data-target="#collapseDocuments"
					aria-expanded="true"
					aria-controls="collapseTwo"
				>
					<i className="fas fa-receipt"></i>
					<span>Documentos</span>
				</a>
				<div
					id="collapseDocuments"
					className="collapse"
					aria-labelledby="Documents"
					data-parent="#accordionSidebar"
				>
					<div className="bg-white py-2 collapse-inner rounded">
						<h6 className="collapse-header">Opciones</h6>
						<Link to="/invoice" className="collapse-item">
							Facturas
						</Link>
						<Link to="/note" className="collapse-item">
							Notas
						</Link>
					</div>
				</div>
			</li>
			<hr className="sidebar-divider" />
			<li className="nav-item">
				<Link to="/client" className="nav-link collapsed">
					<i className="fas fa-question"></i>
					<span>Terceros</span>
				</Link>
			</li>
			{user.roles_id == 1 &&
				<>
					<hr className="sidebar-divider" />
					<li className="nav-item">
						<Link to="/users" className="nav-link collapsed">
							<i className="fas fa-question"></i>
							<span>Usuarios</span>
						</Link>
					</li>
				</>
			}

			{user.roles_id != 3 &&
				<>
					<hr className="sidebar-divider" />
					<div className="sidebar-heading">Entidad</div>
					<li className="nav-item">
						<a
							className="nav-link collapsed"
							data-toggle="collapse"
							href="#"
							data-target="#collapseUtilities"
							aria-expanded="true"
							aria-controls="collapseUtilities"
						>
							<i className="fas fa-fw fa-wrench"></i>
							<span>Par&aacute;metros</span>
						</a>
						<div
							id="collapseUtilities"
							className="collapse"
							aria-labelledby="headingUtilities"
							data-parent="#accordionSidebar"
						>
							<div className="bg-white py-2 collapse-inner rounded">
								<h6 className="collapse-header">Opciones</h6>
								<Link className="collapse-item" to="/resolution">
									Resoluciones
								</Link>
								<Link className="collapse-item" to="/institution">
									Instituciones
								</Link>
								<Link className="collapse-item" to="/dependence">
									Dependencias
								</Link>
								<Link className="collapse-item" to="/provider">
									Proveedores
								</Link>
								<Link className="collapse-item" to="/software">
									Datos del software
								</Link>
							</div>
						</div>
					</li>
				</>
			}
			<hr className="sidebar-divider" />
			<li className="nav-item">
				<a
					className="nav-link collapsed"
					href="/api/help"
					target="_blank"
					aria-expanded="true"
					aria-controls="collapseTwo"
				>
					<i className="fas fa-question"></i>
					<span>Ayuda</span>
				</a>
			</li>
		</>
	);
};
export default SidebarItems;
