import React, { useState } from "react";
import { Tabs, Tab } from "react-bootstrap";
import PendingNote from "./pending-note";
import CreateNote from "./create-note";
import ConsultNote from "./consult-note";

export default function Note() {
    const [key, setKey] = useState("consult");
    return (
        <div className="card shadow mb-4">
            <div className="card-header py-3">
                <h6 className="m-0 font-weight-bold text-primary">
                    Notas Contables
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
                        <PendingNote />
                    </Tab>
                    <Tab eventKey="create" title="Crear">
                        <CreateNote />
                    </Tab>
                    <Tab eventKey="consult" title="Consultar">
                        <ConsultNote />
                    </Tab>
                </Tabs>
            </div>
        </div>
    );
}
