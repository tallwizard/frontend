import React from "react";
import { Formik, Field, Form, ErrorMessage } from "formik";
import * as Yup from "yup";
import { toast } from "react-toastify";

const SignupSchema = Yup.object().shape({
	officeName: Yup.string("Texto invalido")
		.max(200, "M\u00E1ximo 200 caracteres")
		.required("Requerido"),
	typeClient: Yup.string("Seleccion invalida").required("Requerido"),
	typeRegime: Yup.string("Seleccion invalida").required("Requerido"),
	typeDocument: Yup.string("Seleccion invalida").required("Requerido"),
	document: Yup.string()
		.max(100, "M\u00E1ximo 100 caracteres")
		.required("Requerido")
		.matches(/^[0-9]+$/, "Valor numerico invalido"),
	phone: Yup.string()
		.max(100, "M\u00E1ximo 100 caracteres")
		.required("Requerido")
		.matches(/^[0-9]+$/, "Valor numerico invalido"),
	email: Yup.string("Texto invalido")
		.max(100, "M\u00E1ximo 100 caracteres")
		.email("Correo invalido")
		.required("Requerido"),
	address: Yup.string("Texto invalido")
		.max(200, "M\u00E1ximo 200 caracteres")
		.required("Requerido"),
	city: Yup.string("Seleccion invalida").required("Requerido"),
	agentName: Yup.string("Texto invalido")
		.max(200, "M\u00E1ximo 200 caracteres")
		.required("Requerido"),
	agentDocument: Yup.string()
		.max(100, "M\u00E1ximo 100 caracteres")
		.required("Requerido")
		.matches(/^[0-9]+$/, "Valor numerico invalido"),
	emailAutoship: Yup.string("Texto invalido")
		.max(100, "M\u00E1ximo 100 caracteres")
		.email("Correo invalido")
		.required("Requerido"),
	dianTest: Yup.string("Seleccion invalida").required("Requerido"),
	software: Yup.string("Seleccion invalida").required("Requerido"),
});

export default function ProviderForm({
	closeModal,
	dataModal,
	typeClientData,
	typeRegimeData,
	typeDocumentData,
	cityData,
	softwareData,
}) {
	return (
		<div>
			<Formik
				initialValues={{
					id: dataModal.id ? dataModal.id : '',
					officeName: dataModal.office_name ? dataModal.office_name : '',
					typeClient: dataModal.type_clients_id ? dataModal.type_clients_id : '',
					typeRegime: dataModal.type_regimes_id ? dataModal.type_regimes_id : '',
					typeDocument: dataModal.type_documents_id ? dataModal.type_documents_id : '',
					document: dataModal.document ? dataModal.document : '',
					phone: dataModal.phone ? dataModal.phone : '',
					email: dataModal.email ? dataModal.email : '',
					address: dataModal.address ? dataModal.address : '',
					city: dataModal.cities_id ? dataModal.cities_id : '',
					agentName: dataModal.agent_name ? dataModal.agent_name : '',
					agentDocument: dataModal.agent_document ? dataModal.agent_document : '',
					emailAutoship: dataModal.email_autoship ? dataModal.email_autoship : '',
					dianTest: dataModal.dian_test ? dataModal.dian_test : '',
					software: dataModal.software_data_id ? dataModal.software_data_id : '',
				}}
				validationSchema={SignupSchema}
				onSubmit={(values) => {
					if (values.id) {
						axios
							.post("api/provider/update", values)
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
							.post("api/provider/create", values)
							.then((res) => {
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
								<label htmlFor="officeName">
									Nombre de la oficina
								</label>
								<Field
									id="officeName"
									name="officeName"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="officeName"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="typeClient">Tipo tercero</label>
								<select
									className="form-control"
									id="typeClient"
									name="typeClient"
									onChange={props.handleChange}
									value={props.values.typeClient}
								>
									<option value={null}>Seleccione...</option>
									{typeClientData.map((option) => {
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
									name="typeClient"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="typeRegime">Tipo regimen</label>
								<select
									className="form-control"
									id="typeRegime"
									name="typeRegime"
									onChange={props.handleChange}
									value={props.values.typeRegime}
								>
									<option value={null}>Seleccione...</option>
									{typeRegimeData.map((option) => {
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
									name="typeRegime"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="typeDocument">
									Tipo documento
								</label>
								<select
									className="form-control"
									id="typeDocument"
									name="typeDocument"
									onChange={props.handleChange}
									value={props.values.typeDocument}
								>
									<option value={null}>Seleccione...</option>
									{typeDocumentData.map((option) => {
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
									name="typeDocument"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="document">Documento</label>
								<Field
									id="document"
									name="document"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="document"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="phone">Telefono</label>
								<Field
									id="phone"
									name="phone"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="phone"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="email">Correo</label>
								<Field
									id="email"
									name="email"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="email"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="address">Direccion</label>
								<Field
									id="address"
									name="address"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="address"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="city">Ciudad</label>
								<select
									className="form-control"
									id="city"
									name="city"
									onChange={props.handleChange}
									value={props.values.city}
								>
									<option value={null}>Seleccione...</option>
									{cityData.map((option) => {
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
									name="departament"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="agentName">
									Nombre del representante
								</label>
								<Field
									id="agentName"
									name="agentName"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="agentName"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="agentDocument">
									Documento del representante
								</label>
								<Field
									id="agentDocument"
									name="agentDocument"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="agentDocument"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="emailAutoship">
									Correo de autoenvio
								</label>
								<Field
									id="emailAutoship"
									name="emailAutoship"
									type="text"
									className="form-control"
								/>
								<ErrorMessage
									name="emailAutoship"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="dianTest">
									Entorno de prueba
								</label>
								<select
									className="form-control"
									id="dianTest"
									name="dianTest"
									onChange={props.handleChange}
									value={props.values.dianTest}
								>
									<option value={null}>Seleccione...</option>
									<option value={1}>Si</option>
									<option value={2}>No</option>
								</select>
								<ErrorMessage
									name="dianTest"
									className="text-danger"
									component="span"
								/>
							</div>
							<div className="form-group">
								<label htmlFor="software">
									Datos del software
								</label>
								<select
									className="form-control"
									id="software"
									name="software"
									onChange={props.handleChange}
									value={props.values.software}
								>
									<option value={null}>Seleccione...</option>
									{softwareData.map((option) => {
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
									name="software"
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
