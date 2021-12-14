import React from "react";
import { Link } from "react-router-dom";

const NotFound = () => {
    return (
        <div
            style={{ marginTop: "30vh" }}
            className="d-flex justify-content-center"
        >
            <div className="text-center">
                <div className="error mx-auto" data-text="404">
                    404
                </div>
                <p className="lead text-gray-800 mb-5">Pagina no encontrada</p>
                <p className="text-gray-500 mb-0">
                    Esta pagina no se encuentra disponible, inicie sesion
                    nuevamente
                </p>
                <Link to="/">&larr; Volver</Link>
            </div>
        </div>
    );
};
export default NotFound;
