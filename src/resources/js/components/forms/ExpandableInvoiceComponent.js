import React, { useEffect, useState } from "react";
import { toast } from "react-toastify";

const ExpandableInvoiceComponent = (props) => {
    // const [id, setId] = useState();
    const [detail, setDetail] = useState([]);
    useEffect(() => {
        if (props.import == false) {
            // setId(props.data.id);
            async function fetchDetail() {
                await axios
                    .get("api/invoices/detail/" + props.data.id)
                    .then((res) => {
                        setDetail(res.data.data);
                    })
                    .catch((err) => {
                        toast.error(err.response.message);
                        setDetail([]);
                    });
            }
            fetchDetail();
        } else {
            setDetail(props.data.items);
        }
    }, []);

    return (
        <div>
            {detail.map((item, index) => {
                return (
                    <div key={index}>
                        <ul className="list-group py-3 px-5">
                            <li className="list-group-item active">
                                <strong>Item:</strong> {index + 1}
                            </li>
                            <li className="list-group-item">
                                <strong>Codigo del producto:</strong>{" "}
                                {item.productCode}
                            </li>
                            <li className="list-group-item">
                                <strong>Nombre del producto:</strong>{" "}
                                {item.productName}
                            </li>
                            <li className="list-group-item">
                                <strong>Marca del producto:</strong>{" "}
                                {item.productBrand}
                            </li>
                            <li className="list-group-item">
                                <strong>Cantidad del producto:</strong>{" "}
                                {item.productAmount}
                            </li>
                            <li className="list-group-item">
                                <strong>Precio unitario del producto:</strong>{" "}
                                {item.productPrice}
                            </li>
                            <li className="list-group-item">
                                <strong>Descuento del producto:</strong>{" "}
                                {item.productDiscount}
                            </li>
                            <li className="list-group-item">
                                <strong>
                                    Razon del descuento del producto:
                                </strong>{" "}
                                {item.productReasonDiscount}
                            </li>
                        </ul>
                    </div>
                );
            })}
        </div>
    );
};

export default ExpandableInvoiceComponent;
