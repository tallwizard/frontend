import React from "react";
import { Formik, Field, Form, ErrorMessage } from "formik";
import * as Yup from "yup";
import { toast } from "react-toastify";
import moment from "moment";

const SignupSchema = Yup.object().shape({
	code: Yup.string("Texto invalido")
		.min(3, "M\u00EDnimo 3 caracteres")
		.max(10, "M\u00E1ximo 10 caracteres")
		.required("Requerido"),
	number: Yup.string("Texto invalido")
		.max(200, "M\u00E1ximo 200 caracteres")
		.required("Requerido"),
	key: Yup.string("Texto invalido")
		.max(200, "M\u00E1ximo 200 caracteres")
		.required("Requerido"),
	startDate: Yup.date("Fecha Invalida").required("Requerido"),
	endDate: Yup.date("Fecha Invalida")
		.required("Requerido")
		.when("startDate", (startDate) => {
			if (startDate) {
				return Yup.date("Fecha Invalida")
					.min(
						startDate,
						"Fecha minima debe ser " +
						moment(startDate).format("DD/MM/YYYY")
					)
					.required("Requerido");
			}
		}),
	startConsecutive: Yup.number()
		.typeError("Numero invalido")
		.max(9999999999, "M\u00E1ximo 10 caracteres")
		.required("Requerido"),
	endConsecutive: Yup.number()
		.typeError("Numero invalido")
		.max(9999999999, "M\u00E1ximo 10 caracteres")
		.required("Requerido")
		.when("startConsecutive", (startConsecutive) => {
			if (startConsecutive) {
				return Yup.number()
					.typeError("Numero invalido")
					.min(
						startConsecutive + 1,
						"El valor m\u00EDnimo debe ser " +
						(startConsecutive + 1)
					)
					.max(9999999999, "M\u00E1ximo 10 caracteres")
					.required("Requerido");
			}
		}),
	prefix: Yup.string("Texto invalido")
		.max(10, "M\u00E1ximo 10 caracteres")
		.required("Requerido"),
	dependence: Yup.string("Seleccion invalida").required("Requerido"),
	active: Yup.boolean()
});

export default function ResolutionForm({
	closeModal,
	dataModal,
	dependenceData,
}) {
	return (
		<div>
			<Formik
				initialValues={{
					id: dataModal.id ? dataModal.id : '',
					code: dataModal.code ? dataModal.code : '',
					number: dataModal.number ? dataModal.number : '',
					key: dataModal.key ? dataModal.key : '',
					startDate: dataModal.start_date
						? dataModal.start_date
						: moment(new Date()).format("YYYY-MM-DD"),
					endDate: dataModal.end_date
						? dataModal.end_date
						: moment(new Date()).format("YYYY-MM-DD"),
					startConsecutive: dataModal.start_consecutive ? dataModal.start_consecutive : '',
					endConsecutive: dataModal.end_consecutive ? dataModal.end_consecutive : '',
					prefix: dataModal.prefix ? dataModal.prefix : '',
					dependence: dataModal.dependences_id ? dataModal.dependences_id : '',
					active: dataModal.active ? dataModal.active : 0,
				}}
				validationSchema={SignupSchema}
				onSubmit={(values) => {
					if (values.id) {
						axios
							.post("api/resolution/update", values)
							.then((res) => {
								toast.dismiss();
								toast.success(res.data.message);
								closeModal();
							})
							.catch((err) => {
								toast.dismiss();
								let errors = err.response.data.message;
								errors.forEach((element) => {
									toast.error(element, {
										autoClose: false,
									});
								});
							});
					} else {
						axios
							.post("api/resolution/create", values)
							.then((res) => {
								toast.dismiss();
								toast.success(res.data.message);
								closeModal();
							})
							.catch((err) => {
								toast.dismiss();
								let errors = err.response.data.message;
								errors.forEach((element) => {
									toast.error(element, {
										autoClose: false,
									});
								});
							});
					}
				}}
			>
				{(props) => (
					<Form>
						<div className="mx-5">
							<div className="form-group">
								<label htmlFor="code">Codigo</label>
								<Field
									id="code"
									name="code"
									type="text"
									className="form-control text-uppercase"

								/>
								<ErrorMessage
									name="code"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="number">Numero</label>
								<Field
									id="number"
									name="number"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="number"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="key">Llave</label>
								<Field
									id="key"
									name="key"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="key"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="startDate">Fecha inicial</label>
								<Field
									id="startDate"
									name="startDate"
									type="date"
									className="form-control"
								/>
								<ErrorMessage
									name="startDate"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="endDate">Fecha final</label>
								<Field
									id="endDate"
									name="endDate"
									type="date"
									className="form-control"
								/>
								<ErrorMessage
									name="endDate"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="startConsecutive">
									Consecutivo inicial
								</label>
								<Field
									id="startConsecutive"
									name="startConsecutive"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="startConsecutive"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="endConsecutive">
									Consecutivo final
								</label>
								<Field
									id="endConsecutive"
									name="endConsecutive"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="endConsecutive"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="prefix">
									Prefijo de factura
								</label>
								<Field
									id="prefix"
									name="prefix"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="prefix"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="dependence">Dependencia</label>
								<select
									className="form-control"
									id="dependence"
									name="dependence"
									onChange={props.handleChange}
									value={props.values.dependence}
								>
									<option value={null}>Seleccione...</option>
									{dependenceData.map((option) => {
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
								<ErrorMessage
									name="dependence"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label>Estado</label>
								<select
									className="form-control"
									name="active"
									onChange={props.handleChange}
									value={props.values.active}
								>
									<option value={1}>Activo</option>
									<option value={0}>Inactivo</option>
								</select>
								<ErrorMessage
									name="active"
									className="text-danger"
									component="span"
								/>
							</div>
							<button
								type="submit"
								className="btn btn-primary btn-user btn-block my-2"
							>
								{dataModal.id ? "Actualizar" : "Guardar"}
							</button>
							<hr />
						</div>
					</Form>
				)}
			</Formik>
		</div>
	);
}
