import React, { Component } from "react";
import { Modal } from "react-bootstrap";

export default class ModalDialog extends Component {
    constructor(props) {
        super(props);
    }
    render() {
        return (
            <Modal
                show={this.props.show}
                onHide={() => this.props.handleClose()}
                backdrop="static"
                keyboard={false}
            >
                <Modal.Header>
                    <Modal.Title>{this.props.data.title}</Modal.Title>
                </Modal.Header>
                <Modal.Body>{this.props.data.body}</Modal.Body>
                <Modal.Footer>
                    <button
                        className="btn btn-secondary"
                        onClick={() => this.props.closeModal()}
                    >
                        Cerrar
                    </button>
                    <button
                        className="btn btn-primary"
                        onClick={() => this.props.accept()}
                    >
                        Aceptar
                    </button>
                </Modal.Footer>
            </Modal>
        );
    }
}
