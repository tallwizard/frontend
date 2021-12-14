import React, { Component } from "react";
import {  toast } from "react-toastify";
import DataTable from "react-data-table-component";
import { Modal, Spinner } from "react-bootstrap";
import ClientForm from "../../../components/forms/ClientForm";
import ModalDialog from "../../../components/ModalDialog";
import datatableSpanish from "../../../components/static/datatable-locale.json";
import moment from "moment";
const initialValues = {
    id: "",
    name: "",
    lastName: "",
    typeClient: "",
    typeDocument: "",
    document: "",
    phone: "",
    email: "",
    city: "",
    address: "",
};
export default class ConsultClient extends Component {
    constructor(props) {
        super(props);
        this.state = {
            data: [],
            loading: false,
            name: "",
            document: "",
            email: "",
            dateStart: null,
            dateEnd: null,
            typeClientData: [],
            typeDocumentData: [],
            cityData: [],
            showModal: false,
            showModalDelete: false,
            titleModal: "Añadir Datos",
            dataModal: initialValues,
            dataModalDelete: {
                title: "Eliminar Dato",
                body: "¿Esta seguro que desea eliminar este elemento?",
            },
            columns: [
                {
                    name: "Nombres",
                    sortable: true,
                    selector: "name",
                },
                {
                    name: "Apellidos",
                    sortable: true,
                    selector: "last_name",
                },
                {
                    name: "Tipo de tercero",
                    sortable: true,
                    selector: "type_client",
                },
                {
                    name: "Tipo de documento",
                    sortable: true,
                    selector: "type_document",
                },
                {
                    name: "Documento",
                    sortable: true,
                    selector: "document",
                },
                {
                    name: "Telefono",
                    sortable: true,
                    selector: "phone",
                },
                {
                    name: "Correo",
                    sortable: true,
                    selector: "email",
                },
                {
                    name: "Ciudad",
                    sortable: true,
                    selector: "city",
                },
                {
                    name: "Direccion",
                    sortable: true,
                    selector: "address",
                },
                {
                    name: "Acciones",
                    cell: (row) => (
                        <div className="row col-12">
                            <button
                                className="btn btn-secondary col-6"
                                onClick={() => this.openModal(row.id)}
                            >
                                <i className="fas fa-edit"></i>
                            </button>
                            <button
                                className="btn btn-primary col-6"
                                onClick={() => this.openModalDelete(row.id)}
                            >
                                <i className="fas fa-trash"></i>
                            </button>
                        </div>
                    ),
                    ignoreRowClick: true,
                    allowOverflow: true,
                    button: true,
                },
            ],
        };
    }

    fetchData() {
        axios
            .get("api/resource/client")
            .then((res) => {
                if (res.status == 200) {
                    this.setState({
                        typeClientData: res.data.data,
                    });
                }
            })
            .catch((err) => {
                this.setState({
                    typeClientData: [],
                });
                if (err.response.status != 404) {
                    toast.dismiss();
                    toast.error(err.response.data.message, {
                        autoClose: false,
                    });
                }
            });
        axios
            .get("api/resource/document")
            .then((res) => {
                if (res.status == 200) {
                    this.setState({
                        typeDocumentData: res.data.data,
                    });
                }
            })
            .catch((err) => {
                this.setState({
                    typeDocumentData: res.data.data,
                });
                if (err.response.status != 404) {
                    toast.dismiss();
                    toast.error(err.response.data.message, {
                        autoClose: false,
                    });
                }
            });
        axios
            .get("api/resource/city")
            .then((res) => {
                if (res.status == 200) {
                    this.setState({
                        cityData: res.data.data,
                    });
                }
            })
            .catch((err) => {
                this.setState({
                    cityData: [],
                });
                if (err.response.status != 404) {
                    toast.dismiss();
                    toast.error(err.response.data.message, {
                        autoClose: false,
                    });
                }
            });
    }

    componentDidMount() {
        this.fetchData();
    }

    openModalDelete(event) {
        this.setState({
            showModalDelete: true,
            dataIdModalDelete: event,
        });
    }

    async openModal(event) {
        if (event) {
            await axios
                .get("api/clients/" + event)
                .then((res) => {
                    this.setState({
                        titleModal: "Editar Datos",
                        dataModal: res.data.data,
                    });
                })
                .catch((err) => {
                    toast.dismiss();
                    toast.error(err.response.data.message, {
                        autoClose: false,
                    });
                });
        } else {
            this.setState({
                titleModal: "Añadir Datos",
                dataModal: initialValues,
            });
        }
        this.setState({ showModal: true });
    }

    closeModal() {
        this.consult();
        this.setState({ showModal: false });
    }
    closeModalDelete() {
        this.consult();
        this.setState({ showModalDelete: false });
    }
    async acceptModalDelete() {
        await axios
            .get("api/clients/delete/" + this.state.dataIdModalDelete)
            .then((res) => {
                this.closeModalDelete();
                toast.dismiss();
                toast.success(res.data.message);
            })
            .catch((err) => {
                toast.dismiss();
                toast.error(err.response.data.message, {
                    autoClose: false,
                });
            });
    }
    async consult() {
        toast.dismiss();
        if (this.state.dateEnd < this.state.dateStart) {
            toast.dismiss();
            return toast.error(
                "Las fecha final debe ser mayor a: " +
                    moment(this.state.dateStart).format("DD/MM/YYYY"),
                {
                    autoClose: false,
                }
            );
        }
        toast.info("Consultando...", { autoClose: false });
        this.setState({ loading: true });
        await axios
            .post("api/clients/consult", {
                name: this.state.name,
                document: this.state.document,
                email: this.state.email,
                dateStart: this.state.dateStart,
                dateEnd: this.state.dateEnd,
            })
            .then((res) => {
                toast.dismiss();
                if (res.status == 200) {
                    this.setState({
                        data: res.data.data,
                        loading: false,
                    });
                }
            })
            .catch((err) => {
                toast.dismiss();
                toast.error(err.response.data.message, {
                    autoClose: false,
                });
                this.setState({ data: [], loading: false });
            });
    }

    render() {
        return (
            <>
                <div className="my-5 mx-5">
                    <div className="row">
                        <div className="form-group col-2">
                            <label htmlFor="">Nombre</label>
                            <input
                                type="text"
                                className="form-control"
                                onChange={(e) =>
                                    this.setState({ name: e.target.value })
                                }
                            />
                        </div>
                        <div className="form-group col-2">
                            <label htmlFor="">Documento</label>
                            <input
                                type="text"
                                className="form-control"
                                onChange={(e) =>
                                    this.setState({
                                        document: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="form-group col-2">
                            <label htmlFor="">Correo</label>
                            <input
                                type="text"
                                className="form-control"
                                onChange={(e) =>
                                    this.setState({
                                        email: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="form-group col-2">
                            <label htmlFor="">Fecha Inicial</label>
                            <input
                                type="date"
                                className="form-control"
                                onChange={(e) =>
                                    this.setState({
                                        dateStart: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="form-group col-2">
                            <label htmlFor="">Fecha Final</label>
                            <input
                                type="date"
                                className="form-control"
                                onChange={(e) =>
                                    this.setState({
                                        dateEnd: e.target.value,
                                    })
                                }
                            />
                        </div>
                        <div className="form-group col-2">
                            <label htmlFor="">&nbsp;&nbsp;</label>
                            <br />
                            <button
                                className="btn btn-success"
                                onClick={() => this.consult()}
                            >
                                <i className="fas fa-search mr-2 my-auto" />{" "}
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>
                <DataTable
                    className="my-5"
                    noHeader
                    fixedHeader={true}
                    fixedHeaderScrollHeight="50vh"
                    highlightOnHover
                    noDataComponent={
                        <div className="text-secondary my-5">
                            No hay datos que mostrar
                        </div>
                    }
                    striped
                    responsive
                    persistTableHead
                    progressComponent={
                        <Spinner
                            style={{ margin: 50 }}
                            variant="primary"
                            animation="border"
                        >
                            <span className="sr-only">Loading...</span>
                        </Spinner>
                    }
                    pagination
                    title=""
                    columns={this.state.columns}
                    data={this.state.data}
                    paginationComponentOptions={datatableSpanish}
                />
                <Modal
                    scrollable={true}
                    size="md"
                    show={this.state.showModal}
                    onHide={() => this.closeModal()}
                    keyboard={false}
                >
                    <Modal.Header closeButton>
                        <Modal.Title>{this.state.titleModal}</Modal.Title>
                    </Modal.Header>
                    <Modal.Body>
                        <ClientForm
                            cityData={this.state.cityData}
                            typeDocumentData={this.state.typeDocumentData}
                            typeClientData={this.state.typeClientData}
                            dataModal={this.state.dataModal}
                            closeModal={() => this.closeModal()}
                        />
                    </Modal.Body>
                </Modal>
                <ModalDialog
                    data={this.state.dataModalDelete}
                    show={this.state.showModalDelete}
                    closeModal={() => this.closeModalDelete()}
                    accept={() => this.acceptModalDelete()}
                />
            </>
        );
    }
}
