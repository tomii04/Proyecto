<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $email = htmlspecialchars($_POST['email']);
    $mensaje = htmlspecialchars($_POST['mensaje']);

    $rutaArchivo = __DIR__ . '/../Mensajes/mensajes.txt';
    $contenido = "Nombre: $nombre\nEmail: $email\nMensaje:\n$mensaje\n---\n";
    file_put_contents($rutaArchivo, $contenido, FILE_APPEND);

    echo "<h2>Gracias por tu mensaje, $nombre. Lo hemos recibido correctamente.</h2>";
}
?>
