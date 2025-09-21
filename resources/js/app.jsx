// import './bootstrap';
import React from 'react'
import Pos from "./components/Pos";
import { createRoot } from 'react-dom/client';
// export default function app() {
//   return (
//     <Pos />
//   )
// }

// Check for the 'cart' element and render the 'cart' component using createRoot
if (document.getElementById("cart")) {
    const cartRoot = createRoot(document.getElementById("cart"));
    cartRoot.render(<Pos />);
}