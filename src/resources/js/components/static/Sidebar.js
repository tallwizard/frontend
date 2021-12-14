import React from "react";
import SidebarItems from "./SidebarItems";
import iconSidebar from "../../../img/sinergia.svg";

class Sidebar extends React.Component {
	sidebarToggle() {
		$("body").toggleClass("sidebar-toggled");
		$(".sidebar").toggleClass("toggled");
		if ($(".sidebar").hasClass("toggled")) {
			$(".sidebar .collapse").collapse("hide");
		}
	}
	
	render() {
		return (
			<>
				<ul
					className="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion"
					id="accordionSidebar"
				>
					<a
						className="sidebar-brand d-flex align-items-center justify-content-center"
						href="#"
					>
						<div className="sidebar-brand-icon">
							<img
								src={iconSidebar}
								width="30px"
								height="40px"
							></img>
						</div>
						<div className="sidebar-brand-text mx-3">
							SINERGIA<sup>S.A.S</sup>
						</div>
					</a>
					<hr className="sidebar-divider my-0" />
					<SidebarItems />
					<hr className="sidebar-divider d-none d-md-block" />
					<div className="text-center d-none d-md-inline">
						<button
							className="rounded-circle border-0"
							id="sidebarToggle"
							onClick={this.sidebarToggle}
						></button>
					</div>
				</ul>
			</>
		);
	}
}

export default Sidebar;
