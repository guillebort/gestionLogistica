function EnviarCarrito(url, valores) {
    const options = {
        method: "POST",
        headers: {
            "Content-Type": "application/json; charset=utf-8"
        },
        body: JSON.stringify(valores)
    };
    
    fetch(url, options)
        .then(response => response.json()) 
        .then(data => {
            // Ampliamos la validación para aceptar "ok" o "success"
            if (data.status === "ok" || data.status === "success") {
                // Soportamos tanto la ruta antigua como el nuevo estándar REST
                const destino = data.redirect || (data.data && data.data.redirect);
                if (destino) {
                    window.location.href = destino;
                } else {
                    console.warn("El servidor no envió una ruta de destino.");
                }
            } else {
                alert("Error: " + (data.message || "Fallo al procesar el carrito"));
            }
        })
        .catch(error => {
            console.error("Error en la petición:", error);
            alert("No se pudo conectar con el servidor.");
        });
}