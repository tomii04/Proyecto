<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cooperativa de Viviendas</title>
  <link rel="stylesheet" href="estilo.css">
  <link rel="icon" href="favicon.ico" />
</head>
<body>
  <header role="banner" class="header-fixed">
    <div class="top-header container">
      <a href="#" class="logo-link" aria-label="Página principal Cooperativa">
        <img src="imagenes/logo.jpeg" alt="Logo Cooperativa" class="logo" />
      </a>
      <nav class="menu" role="navigation" aria-label="Menú principal">
        <ul>
          <li><a href="#">Inicio</a></li>
          <li><a href="#quienes-somos">¿Quiénes somos?</a></li>
          <li><a href="#beneficios">Beneficios</a></li>
          <li><a href="#nuestra-historia">Nuestra Historia</a></li>
          <li><a href="#proyectos">Proyectos</a></li>
          <li><a href="#contacto">Contacto</a></li>
          <li><a href="testlogin.php">Ingresar / Registrarse</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <section class="hero" aria-labelledby="hero-title">
    <div class="container hero-content">
      <h1 id="hero-title">Cooperativa de Viviendas</h1>
      <p>Creemos en la autogestión, la ayuda mutua y el derecho de todas las familias a una vivienda digna. Nuestro modelo cooperativo construye más que casas: construye comunidad.</p>
      <a href="#proyectos" class="boton">Conocé nuestros proyectos</a>
    </div>
  </section>

  <section id="quienes-somos" class="container">
    <h2>¿Quiénes somos?</h2>
    <p>Somos una cooperativa de viviendas de ayuda mutua comprometida con el trabajo comunitario y la solidaridad.</p>
  </section>

  <section id="beneficios" class="container">
    <h2>Beneficios</h2>
    <ul>
      <li>Acceso a viviendas dignas.</li>
      <li>Participación en decisiones.</li>
      <li>Trabajo en comunidad.</li>
    </ul>
  </section>

  <main>
    <section id="nuestra-historia" class="valores">
      <div class="container">
        <h2>Nuestros pilares</h2>
        <div class="grid">
          <article class="item"><h3>Autogestión</h3><p>Las decisiones se toman en conjunto por medio de asambleas democráticas.</p></article>
          <article class="item"><h3>Ayuda mutua</h3><p>El trabajo compartido es parte del proceso de construcción y fortalecimiento del colectivo.</p></article>
          <article class="item"><h3>Participación</h3><p>Cada socio aporta desde su compromiso, construyendo una comunidad activa y solidaria.</p></article>
        </div>
      </div>
    </section>

    <section id="proyectos" class="galeria">
      <div class="container">
        <h2>Viviendas y Comunidad</h2>
        <div class="imagenes">
          <img src="imagenes/fondo.jpg" alt="Proyecto habitacional" />
          <img src="imegenes/constru.jpeg" alt="Obra en construcción" />
        </div>
      </div>
    </section>

    <section class="proyectos-destacados">
      <div class="container">
        <h2>Proyectos Destacados</h2>
        <div class="grid">
          <article class="item"><img src="imagenes/1.webp" alt="Proyecto Nuevo Horizonte" /><h3>Nuevo Horizonte</h3><p>Desarrollo sostenible con espacios verdes y áreas comunes.</p></article>
          <article class="item"><img src="imagenes/2.jpg" alt="Proyecto Comunidad Solidaria" /><h3>Comunidad Solidaria</h3><p>Espacios diseñados para fortalecer la participación comunitaria.</p></article>
          <article class="item"><img src="imagenes/3.png" alt="Proyecto Vida y Familia" /><h3>Vida y Familia</h3><p>Un entorno ideal para familias jóvenes y adultos mayores.</p></article>
        </div>
      </div>
    </section>

    <section id="contacto" class="contacto">
      <div class="container">
        <h2>📩 Contacto</h2>
        <form class="form-contacto" action="https://formsubmit.co/samaosesi@gmail.com" method="POST">
          <input type="hidden" name="_captcha" value="false" />
          <label>Tu nombre</label><input type="text" name="nombre" required />
          <label>Tu email</label><input type="email" name="email" required />
          <label>Tu mensaje</label><textarea name="mensaje" rows="5" required></textarea>
          <button type="submit" class="boton">Enviar mensaje</button>
        </form>
      </div>
    </section>
  </main>

  <footer role="contentinfo" class="footer">
    <div class="container footer-content">
      <p>&copy; 2025 Cooperativa de Viviendas - Todos los derechos reservados.</p>
    </div>
  </footer>
</body>
</html>
