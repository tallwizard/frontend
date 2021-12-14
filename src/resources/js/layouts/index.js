import React from "react";
import NotFound from "../components/NotFound";
import Footer from "../components/static/Footer";
import Sidebar from "../components/static/Sidebar";
import Topbar from "../components/static/Topbar";

const UserLayout = ({ children }, props) => (
	<>
		<div id="wrapper">
			<Sidebar />
			<div id="content-wrapper" className="d-flex flex-column">
				<div id="content">
					<Topbar props={props} />
					<div className="container-fluid">{children}</div>
				</div>
				<Footer />
			</div>
		</div>
	</>
);

export default UserLayout;
