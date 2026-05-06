function EnviarCarrito(url, valores) {
    const options = {
        method: "POST",
        headers: {
            "Content-Type": "application/json; charset=utf-8"
        },
        body: JSON.stringify(valores)
    };
    
    fetch(url, options)
        .then(response => response.json()) // 1. Cambiamos .text() por .json()
        .then(data => {
            // 2. Si el servidor nos dice que todo OK, redirigimos de verdad
            if (data.status === "ok") {
                window.location.href = data.redirect;
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error en la petición:", error);
            alert("No se pudo conectar con el servidor.");
        });
}