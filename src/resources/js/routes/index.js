import React from "react";
import { BrowserRouter as Router, Redirect, Route, Switch } from "react-router-dom";
// layouts
import UserLayout from "../layouts";
// pages
import Welcome from "../pages/welcome";
import Login from "../pages/login";
import Invoice from "../pages/invoice";
import Software from "../pages/software";
import Provider from "../pages/provider";
import Institution from "../pages/institution";
import Dependence from "../pages/dependence";
import Resolution from "../pages/resolution";
import Note from "../pages/note";
import Client from "../pages/client";
import User from "../pages/user";
import NotFound from "../components/NotFound";
import { ToastContainer, Zoom } from "react-toastify";

import axios from 'axios';

axios.interceptors.request.use((request) => {
	const token = localStorage.getItem("token");
	if (token) {
		request.headers.Authorization = "Bearer " + token;
	}
	return request
})

axios.interceptors.response.use((response) => {
	return response
}, (error) => {
	if (error.response) {
		if (error.response.status == 401) {
			if (localStorage.getItem("token")) {
				localStorage.clear()
				return window.location.href = '/login'
			}
		}
	}
	return Promise.reject(error);
})

export default function Routes() {
	return (
		<Router>
			<ToastContainer
				autoClose={8000}
				transition={Zoom} />
			<Switch>
				<Route exact path="/" component={Welcome} />
				<Route exact path="/login" component={Login} />
				<RouteWrapper
					exact
					path="/users"
					component={User}
					layout={UserLayout}
					needAuth={true}
				/>
				<RouteWrapper
					exact
					path="/invoice"
					component={Invoice}
					layout={UserLayout}
					needAuth={true}
				/>
				<RouteWrapper
					exact
					path="/software"
					component={Software}
					layout={UserLayout}
					needAuth={true}
				/>
				<RouteWrapper
					exact
					path="/provider"
					component={Provider}
					layout={UserLayout}
					needAuth={true}
				/>
				<RouteWrapper
					exact
					path="/institution"
					component={Institution}
					layout={UserLayout}
					needAuth={true}
				/>
				<RouteWrapper
					exact
					path="/dependence"
					component={Dependence}
					layout={UserLayout}
					needAuth={true}
				/>
				<RouteWrapper
					exact
					path="/resolution"
					component={Resolution}
					layout={UserLayout}
					needAuth={true}
				/>
				<RouteWrapper
					exact
					path="/note"
					component={Note}
					layout={UserLayout}
					needAuth={true}
				/>
				<RouteWrapper
					exact
					path="/client"
					component={Client}
					layout={UserLayout}
					needAuth={true}
				/>
				<Route path="*" exact={true} component={NotFound} />
			</Switch>

		</Router>
	);
}
function RouteWrapper({
	component: Component,
	layout: Layout,
	needAuth,
	...rest
}) {
	return (
		<Route
			{...rest}
			render={(props) =>
				localStorage.getItem("token") != null ? (
					<Layout {...props}>
						<Component {...props} />
					</Layout>
				) : (
					<NotFound />
				)
			}
		/>
	);
}
