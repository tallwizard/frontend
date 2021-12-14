import React from "react";
import { Formik, Field, Form, ErrorMessage } from "formik";
import * as Yup from "yup";
import { toast } from "react-toastify";

const SignupSchema = Yup.object().shape({
    name: Yup.string("Texto invalido")
        .max(200, "M\u00E1ximo 200 caracteres")
        .required("Requerido"),
    pin: Yup.string("Texto invalido")
        .max(200, "M\u00E1ximo 200 caracteres")
        .required("Requerido"),
    identification: Yup.string("Texto invalido")
        .max(200, "M\u00E1ximo 200 caracteres")
        .required("Requerido"),
    testId: Yup.string("Texto invalido")
        .max(200, "M\u00E1ximo 200 caracteres")
        .required("Requerido"),
});

export default function SoftwareForm({ closeModal, dataModal }) {
    return (
        <div>
            <Formik
                initialValues={{
                    id: dataModal.id,
                    name: dataModal.name,
                    pin: dataModal.pin,
                    identification: dataModal.identification,
                    testId: dataModal.test_id,
                }}
                validationSchema={SignupSchema}
                onSubmit={(values) => {
                    if (values.id) {
                        axios
                            .post("api/software/update", values)
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
                            .post("api/software/create", values)
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
                <Form>
                    <div className="mx-5">
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
                            <label htmlFor="pin">PIN</label>
                            <Field
                                id="pin"
                                name="pin"
                                type="text"
                                className="form-control"
                            />
                            <ErrorMessage
                                name="pin"
                                className="text-danger"
                                component="span"
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="identification">
                                Identificacion
                            </label>
                            <Field
                                id="identification"
                                name="identification"
                                type="text"
                                className="form-control"
                            />
                            <ErrorMessage
                                name="identification"
                                className="text-danger"
                                component="span"
                            />
                        </div>
                        <div className="form-group">
                            <label htmlFor="testId">Test ID</label>
                            <Field
                                id="testId"
                                name="testId"
                                type="text"
                                className="form-control"
                            />
                            <ErrorMessage
                                name="testId"
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
            </Formik>
        </div>
    );
}
