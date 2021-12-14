import React from "react";
import { Formik, Field, Form, ErrorMessage } from "formik";
import * as Yup from "yup";
import { toast } from "react-toastify";

const SignupSchema = Yup.object().shape({
    name: Yup.string("Texto invalido")
        .max(200, "M\u00E1ximo 200 caracteres")
        .required("Requerido"),
    lastName: Yup.string("Texto invalido")
        .max(200, "M\u00E1ximo 200 caracter"),
    typeClient: Yup.string("Seleccion invalida").required("Requerido"),
    typeDocument: Yup.string("Seleccion invalida").required("Requerido"),
    document: Yup.string()
        .max(100, "M\u00E1ximo 100 caracteres")
        .required("Requerido")
        .matches(/^[0-9]+$/, "Valor numerico invalido"),
    phone: Yup.string()
        .max(20, "M\u00E1ximo 50 caracteres")
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
});

export default function SoftwareForm({
    closeModal,
    dataModal,
    typeClientData,
    typeDocumentData,
    cityData,
}) {
    return (
        <div>
            <Formik
                initialValues={{
                    id: dataModal.id,
                    name: dataModal.name,
                    lastName: dataModal.last_name ? dataModal.last_name : "",
                    typeClient: dataModal.type_clients_id,
                    typeDocument: dataModal.type_documents_id,
                    document: dataModal.document,
                    phone: dataModal.phone,
                    email: dataModal.email,
                    city: dataModal.cities_id,
                    address: dataModal.address,
                }}
                validationSchema={SignupSchema}
                onSubmit={(values) => {
                    if (values.id) {
                        axios
                            .post("api/clients/update", values)
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
                            .post("api/clients/create", values)
                            .then((res) => {
                                toast.dismiss();
                                toast.success(res.data.message);
                                closeModal();
                            })
                            .catch((err) => {
                                toast.dismiss();
                                if (err.response.status == 400) {
                                    let errors = err.response.data.message;
                                    errors.forEach((element) => {
                                        toast.error(element, {
                                            autoClose: false,
                                        });
                                    });
                                } else {
                                    toast.error(err.response.data.message, {
                                        autoClose: false,
                                    });
                                }
                            });
                    }
                }}
            >
                {(props) => (
                    <Form>
                        <div className="mx-5">
                            <div className="form-group">
                                <label htmlFor="name">Nombres</label>
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
                                <label htmlFor="lastName">Apellidos</label>
                                <Field
                                    id="lastName"
                                    name="lastName"
                                    type="text"
                                    className="form-control"
                                />
                                <ErrorMessage
                                    name="lastName"
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
                                    name="city"
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
