// libs
require("./bootstrap");
import React from "react";
import ReactDOM from "react-dom";
// styles
import "bootstrap/dist/css/bootstrap.min.css";
import "react-toastify/dist/ReactToastify.css";
import "./app.css";
import { setDefaultLocale } from "react-datepicker";
import es from "date-fns/locale/es";
setDefaultLocale("es", es);
// routes
import Routes from "./routes";

ReactDOM.render(
	<Routes />,
	document.getElementById("root")
);
