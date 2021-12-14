import React from "react";
import { Formik, Field, Form, ErrorMessage } from "formik";
import * as Yup from "yup";
import { toast } from "react-toastify";

const SignupSchema = Yup.object().shape({
	code: Yup.string("Texto invalido")
		.max(20, "M\u00E1ximo 20 caracteres")
		.required("Requerido"),
	name: Yup.string("Texto invalido")
		.max(200, "M\u00E1ximo 200 caracteres")
		.required("Requerido"),
	institution: Yup.string("Seleccion invalida").required("Requerido"),
});

export default function DependenceForm({
	closeModal,
	dataModal,
	institutionData,
}) {
	return (
		<div>
			<Formik
				initialValues={{
					id: dataModal.id ? dataModal.id : '',
					code: dataModal.code ? dataModal.code : '',
					name: dataModal.name ? dataModal.name : '',
					institution: dataModal.institutions_id ? dataModal.institutions_id : '',
				}}
				validationSchema={SignupSchema}
				onSubmit={(values) => {
					if (values.id) {
						axios
							.post("api/dependence/update", values)
							.then((res) => {
								toast.dismiss();
								toast.success(res.data.message);
								closeModal();
							})
							.catch((err) => {
								toast.dismiss();
								let errors = err.response.data.message;
								errors.forEach((element) => {
									toast.error(element);
								});
							});
					} else {
						axios
							.post("api/dependence/create", values)
							.then((res) => {
								toast.dismiss();
								toast.success(res.data.message);
								closeModal();
							})
							.catch((err) => {
								toast.dismiss();
								let errors = err.response.data.message;
								errors.forEach((element) => {
									toast.error(element);
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
									className="form-control"
								/>
								<ErrorMessage
									name="code"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="name">Nombre</label>
								<Field
									id="name"
									name="name"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="name"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="institution">Institucion</label>
								<select
									className="form-control"
									id="institution"
									name="institution"
									onChange={props.handleChange}
									value={props.values.institution}
								>
									<option value={null}>Seleccione...</option>
									{institutionData.map((option) => {
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
									name="institution"
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
