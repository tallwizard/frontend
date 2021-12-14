import React, { useEffect, useState } from "react";
import { withFormik, ErrorMessage, FieldArray, Field } from "formik";
import * as Yup from "yup";
import "react-datepicker/dist/react-datepicker.css";
import { toast } from "react-toastify";
import axios from "axios";

function CreateNote({ handleSubmit, handleChange, values }) {
	const [prefix, setPrefix] = useState([]);
	const [concept, setConcept] = useState([]);
	const [balance, setBalance] = useState(0);


	const changeBalance = async (e) => {
		let prefix = e.target.name == 'prefixInvoice' ? e.target.value : values.prefixInvoice
		let consecutive = e.target.name == 'consecutive' ? e.target.value : values.consecutive
		const res = await axios.post(`api/invoices/balance`, {
			prefix, consecutive
		}).then(res => {
			if (res.data.data != null) {


				setBalance(res.data.data.balance)
			}
		})
	}

	const formatter = (number) => {
		return new Intl.NumberFormat('en-US', {
			style: 'currency',
			currency: 'USD',
		}).format(number)
	}

	useEffect(() => {
		async function fetchData() {
			axios.get("api/invoices/prefix")
				.then((res) => {
					setPrefix(res.data.data);
				})
				.catch((err) => {
					if (
						err.response.status == 404 ||
						err.response.status == 500
					) {
						setPrefix([]);
					}
					toast.error("Error al cargar los prefijos");
				});
			axios.get("api/notes/concept")
				.then((res) => {
					setConcept(res.data.data);
				})
				.catch((err) => {
					if (
						err.response.status == 404 ||
						err.response.status == 500
					) {
						setConcept([]);
					}
					toast.error("Error al cargar los conceptos");
				});
		}
		fetchData();
	}, []);
	return (
		<>
			<form onSubmit={handleSubmit} noValidate className="my-5 mx-5">
				<div>
					<div className="row">
						<div className="form-group col-5">
							<label htmlFor="prefix">Prefijo</label>
							<input
								onChange={handleChange}
								value={values.prefix}
								id="prefix"
								name="prefix"
								type="text"
								readOnly={true}
								className="form-control"
							/>
							<ErrorMessage name="prefix">
								{(errMsg) => (
									<span className="text-danger">
										{errMsg}
									</span>
								)}
							</ErrorMessage>
						</div>
						<div className="form-group col-4">
							<label htmlFor="prefixInvoice">
								Prefijo de factura
							</label>
							<select
								name="prefixInvoice"
								id="prefixInvoice"
								className="form-control"
								value={values.prefixInvoice}
								onChange={(e) => { handleChange(e); changeBalance(e) }}
							>
								<option value="">Seleccione...</option>
								{prefix.map((option) => {
									return (
										<option
											key={option.code}
											value={option.code}
										>
											{option.code}
										</option>
									);
								})}
							</select>
							<ErrorMessage name="prefixInvoice">
								{(errMsg) => (
									<span className="text-danger">
										{errMsg}
									</span>
								)}
							</ErrorMessage>
						</div>
						<div className="form-group col-3">
							<label htmlFor="consecutive">
								Consecutivo de factura
							</label>
							<input
								onChange={(e) => { handleChange(e); changeBalance(e) }}
								value={values.consecutive}
								id="consecutive"
								name="consecutive"
								type="text"
								className="form-control"
							/>
							<ErrorMessage name="consecutive">
								{(errMsg) => (
									<span className="text-danger">
										{errMsg}
									</span>
								)}
							</ErrorMessage>
						</div>
					</div>
					<div className="row">
						<div className="form-group col-12">
							<label htmlFor="description">
								Descripci&oacute;n
							</label>
							<textarea
								className="form-control"
								id="description"
								name="description"
								rows="3"
								value={values.description}
								onChange={handleChange}
							></textarea>
							<ErrorMessage name="description">
								{(errMsg) => (
									<span className="text-danger">
										{errMsg}
									</span>
								)}
							</ErrorMessage>
						</div>
					</div>
					<div className="row">
						<div className="form-group col-4">
							<label htmlFor="concept">Concepto</label>
							<select
								name="concept"
								id="concept"
								className="form-control"
								value={values.concept}
								onChange={handleChange}
							>
								<option value="">Seleccione...</option>
								{concept.map((option) => {
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
							<ErrorMessage name="concept">
								{(errMsg) => (
									<span className="text-danger">
										{errMsg}
									</span>
								)}
							</ErrorMessage>
						</div>
						<div className="form-group col-4">
							<label htmlFor="total">Total</label>
							<input
								type="text"
								className="form-control"
								name="total"
								disabled={values.concept != 2 ? false : true}
								id="total"
								value={values.concept == 2 ? "" : values.total}
								onChange={handleChange}
							/>
							<ErrorMessage name="total">
								{(errMsg) => (
									<span className="text-danger">
										{errMsg}
									</span>
								)}
							</ErrorMessage>
						</div>
						<div className="form-group col-4">
							<label htmlFor="balance">Saldo</label>
							<input
								type="text"
								className="form-control"
								name="balance"
								disabled={true}
								id="balance"
								value={formatter(balance)}
							/>
							<ErrorMessage name="total">
								{(errMsg) => (
									<span className="text-danger">
										{errMsg}
									</span>
								)}
							</ErrorMessage>
						</div>
					</div>
				</div>
				<button
					type="submit"
					className="btn btn-primary btn-user btn-block my-2"
				>
					Guardar
				</button>
			</form>
		</>
	);
}

export default withFormik({
	mapPropsToValues: () => ({
		prefix: "NCC",
		prefixInvoice: "",
		consecutive: "",
		description: "",
		concept: "",
		total: "",
	}),
	validationSchema: Yup.object().shape({
		prefix: Yup.string("Debe ser de tipo texto").required("Requerido"),
		prefixInvoice: Yup.string("Debe ser de tipo texto").required(
			"Requerido"
		),
		consecutive: Yup.number()
			.typeError("Debe ser de tipo numerico")
			.required("Requerido"),
		concept: Yup.string("Debe ser de tipo texto").required("Requerido"),
		total: Yup.number()
			.typeError("Debe ser de tipo numerico")
			.when("concept", (concept) => {
				if (concept != 2) {
					return Yup.number()
						.typeError("Debe ser de tipo numerico")
						.required("Requerido");
				}
			}),
		description: Yup.string("Debe ser de tipo texto")
			.required("Requerido")
			.max(1000, "Maximo 1000 caracteres"),
	}),
	handleSubmit: (values, { setSubmitting, resetForm }) => {
		axios.post("api/notes/validate/invoice", values)
			.then((res) => {
				if (res.status == 200) {
					axios.post("api/notes/create", values)
						.then((res) => {
							resetForm({ values: "" });
							toast.dismiss();
							toast.success(res.data.data, {
								autoClose: false,
							});
							setBalance(0)
						})
						.catch((err) => {
							if (err.response.data.data) {
								toast.dismiss();
								toast.error(err.response.data.data);
							} else {
								toast.dismiss();
								let obj = Object.values(
									err.response.data.errors
								);
								obj.map((e) => {
									toast.error(JSON.stringify(e));
								});
							}
						});
				}
			})
			.catch((err) => {
				if (err.response.data.message) {
					toast.dismiss();
					toast.error(err.response.data.message);
				} else {
					toast.dismiss();
					let obj = Object.values(err.response.data.errors);
					obj.map((e) => {
						toast.error(JSON.stringify(e));
					});
				}
			});

		setSubmitting(false);
	},
})(CreateNote);
