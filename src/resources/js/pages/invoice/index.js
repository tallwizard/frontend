import React, { useState } from "react";
import { Tabs, Tab } from "react-bootstrap";
import PendingInvoice from "./pending-invoice";
import CreateInvoice from "./create-invoice";
import ImportInvoice from "./import-invoice";
import ConsultInvoice from "./consult-invoice";
import AutoComplete from "../../components/Autocomplete";

export default function Invoice() {
    const [key, setKey] = useState("create");
    return (
        <div className="card shadow mb-4">
            <div className="card-header py-3">
                <h6 className="m-0 font-weight-bold text-primary">
                    Facturas Electr&oacute;nicas
                </h6>
            </div>
            <div className="card-body">
                <Tabs
                    id="controlled-tab-example"
                    activeKey={key}
                    onSelect={(k) => setKey(k)}
                    unmountOnExit={true}
                >
                    <Tab eventKey="loading" title="Pendientes">
                        <PendingInvoice />
                    </Tab>
                    <Tab eventKey="create" title="Crear">
                        <CreateInvoice />
                    </Tab>
                    <Tab eventKey="import" title="Importar">
                        <ImportInvoice />
                    </Tab>
                    <Tab eventKey="consult" title="Consultar">
                        <ConsultInvoice />
                    </Tab>
            
                </Tabs>
            </div>
        </div>
    );
}
