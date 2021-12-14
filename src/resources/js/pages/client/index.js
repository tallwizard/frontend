import React, { useState } from "react";
import { Tabs, Tab } from "react-bootstrap";
import ImportClient from "./import-client";
import CreateClient from "./create-client";
import ConsultClient from "./consult-client";

export default function Client() {
    const [key, setKey] = useState("create");
    return (
        <div className="card shadow mb-4">
            <div className="card-header py-3">
                <h6 className="m-0 font-weight-bold text-primary">Terceros</h6>
            </div>
            <div className="card-body">
                <Tabs
                    id="controlled-tab-example"
                    activeKey={key}
                    onSelect={(k) => setKey(k)}
                    unmountOnExit={true}
                >
                    <Tab eventKey="create" title="Crear">
                        <CreateClient />
                    </Tab>
                    <Tab eventKey="import" title="Importar">
                        <ImportClient />
                    </Tab>
                    <Tab eventKey="consult" title="Consultar">
                        <ConsultClient />
                    </Tab>
                </Tabs>
            </div>
        </div>
    );
}
