<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cooperativa de Viviendas</title>
  <link rel="stylesheet" href="estilo1.css">
  <link rel="icon" href="favicon.ico" />
  <style>
    .section-text, .item, .imagenes, .contacto, .ubicacion {
      opacity: 0;
      transform: translateY(40px);
      transition: all 0.8s ease-out;
    }
    .visible {
      opacity: 1;
      transform: translateY(0);
    }
    .ubicacion {
      margin: 50px 0;
      text-align: center;
    }
    .ubicacion iframe {
      width: 100%;
      max-width: 800px;
      height: 400px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    .hamburger {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      width: 28px;
      height: 22px;
      background: none;
      border: none;
      cursor: pointer;
      z-index: 1100;
    }
    .hamburger span {
      display: block;
      height: 3px;
      width: 100%;
      background: white;
      border-radius: 2px;
      transition: all 0.3s ease;
    }
    .hamburger.active span:nth-child(1) {
      transform: rotate(45deg) translateY(8px);
    }
    .hamburger.active span:nth-child(2) {
      opacity: 0;
    }
    .hamburger.active span:nth-child(3) {
      transform: rotate(-45deg) translateY(-8px);
    }

    .menu {
       position: fixed;
       top: 0;
       right: -300px;
       width: 300px;
       height: 100%;
       background: rgba(242, 87, 48, 0.9); 
       backdrop-filter: blur(6px);          
       display: flex;
       flex-direction: column;
       padding: 80px 20px;
       gap: 20px;
       transition: right 0.3s ease;
       z-index: 1050;
       border-left: 2px solid rgba(255,255,255,0.2); 
    }
    .menu.active {
      right: 0;
    }
    .menu.active {
  right: 0;
}

.hamburger.active {
  display: none;
}

    .menu ul {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .menu a {
      text-decoration: none;
      color: white;
      font-weight: 600;
      padding: 10px 20px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    .menu a:hover {
      background: rgba(255,255,255,0.2);
      transform: scale(1.05);
    }

    .hamburger-close {
      position: absolute;
      top: 20px;
      right: 20px;
      background:none;
      border:none;
      font-size:2rem;
      color:white;
      cursor:pointer;
      z-index:1101;
    }
  </style>
</head>
<body>
  <header role="banner" class="header-fixed">
    <div class="top-header container">
      <a href="#" class="logo-link" aria-label="Página principal Cooperativa">
        <img src="imagenes/logo.jpeg" alt="Logo Cooperativa" class="logo" />
      </a>

      <button class="hamburger" aria-label="Abrir menú" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <nav class="menu" role="navigation" aria-label="Menú principal">
  <button class="hamburger-close" aria-label="Cerrar menú">&times;</button>
  <ul>
    <li><a href="#inicio">Inicio</a></li>
    <li><a href="#quienes-somos">¿Quiénes somos?</a></li>
    <li><a href="#beneficios">Beneficios</a></li>
    <li><a href="#nuestra-historia">Nuestra Historia</a></li>
    <li><a href="#proyectos">Proyectos</a></li>
    <li><a href="#proyectos-destacados">Destacados</a></li>
    <li><a href="#ubicacion">Ubicación</a></li>
    <li><a href="#contacto">Contacto</a></li>
    <li><a href="../Backoffice/login.php">Ingresar / Registrarse</a></li>
  </ul>
</nav>
</div>
</header>

  <section id="inicio" class="hero" aria-labelledby="hero-title">
    <div class="container hero-content">
      <h1 id="hero-title">Cooperativa de Viviendas</h1>
      <p>Creemos en la autogestión, la ayuda mutua y el derecho de todas las familias a una vivienda digna. Nuestro modelo cooperativo construye más que casas: construye comunidad.</p>
      <a href="#proyectos" class="boton">Conocé nuestros proyectos</a>
    </div>
  </section>

  <div class="info-flex" style="display:flex; gap:40px; flex-wrap:wrap; margin-top:40px;">
    <section id="quienes-somos" class="container section-text" style="flex:1; min-width:280px;">
      <h2>¿Quiénes somos?</h2>
      <p>Somos una cooperativa de viviendas de ayuda mutua, formada por familias comprometidas con el desarrollo comunitario. Nuestra filosofía se basa en la solidaridad, el trabajo conjunto y la participación activa en la toma de decisiones.</p>
    </section>

    <section id="beneficios" class="container section-text" style="flex:1; min-width:280px;">
      <h2>Beneficios de formar parte de nuestra cooperativa</h2>
      <ul>
        <li>Acceso a viviendas dignas y seguras.</li>
        <li>Participación activa en decisiones y proyectos comunitarios.</li>
        <li>Trabajo colaborativo y fortalecimiento de la comunidad.</li>
        <li>Capacitaciones y apoyo para el desarrollo personal y familiar.</li>
        <li>Espacios de encuentro y actividades sociales.</li>
      </ul>
    </section>
  </div>

  <main>
    <section id="nuestra-historia" class="valores container section-text">
      <h2>Nuestros pilares</h2>
      <div class="grid">
        <article class="item">
          <h3>Autogestión</h3>
          <p>Las decisiones se toman de manera democrática en asambleas donde cada socio tiene voz y voto, fomentando la responsabilidad colectiva.</p>
        </article>
        <article class="item">
          <h3>Ayuda mutua</h3>
          <p>El trabajo compartido fortalece los lazos comunitarios y permite avanzar en los proyectos de forma eficiente y solidaria.</p>
        </article>
        <article class="item">
          <h3>Participación</h3>
          <p>Cada socio contribuye con su compromiso, habilidades y experiencia, generando un entorno activo y solidario para todos.</p>
        </article>
      </div>
    </section>

    <section id="proyectos" class="galeria container section-text">
      <h2>Viviendas y Comunidad</h2>
      <div class="imagenes">
        <img src="imagenes/fondo.jpg" alt="Proyecto habitacional" />
      </div>
    </section>

    <section id="proyectos-destacados" class="proyectos-destacados container section-text">
      <h2>Proyectos Destacados</h2>
      <div class="grid">
        <article class="item">
          <img src="imagenes/1.webp" alt="Proyecto Nuevo Horizonte" />
          <h3>Nuevo Horizonte</h3>
          <p>Desarrollo sostenible con espacios verdes, áreas comunes y diseño pensado en la calidad de vida de los vecinos.</p>
        </article>
        <article class="item">
          <img src="imagenes/2.jpg" alt="Proyecto Comunidad Solidaria" />
          <h3>Comunidad Solidaria</h3>
          <p>Proyectos que fomentan la participación y colaboración entre los socios, fortaleciendo la identidad de la cooperativa.</p>
        </article>
        <article class="item">
          <img src="imagenes/3.png" alt="Proyecto Vida y Familia" />
          <h3>Vida y Familia</h3>
          <p>Entornos ideales para familias jóvenes y adultos mayores, con espacios seguros y confortables.</p>
        </article>
      </div>
    </section>

    <section id="ubicacion" class="ubicacion section-text">
      <h2>📍 Ubicación</h2>
      <p>Estamos en Uruguay. Vení a conocernos o visitá nuestros proyectos.</p>
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3681422.476963224!2d-58.440913318834844!3d-32.52277997868882!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95bfa5094c9d7f13%3A0x9dbfc1cf05e81d86!2sUruguay!5e0!3m2!1ses-419!2suy!4v1700000000000!5m2!1ses-419!2suy"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
    </section>

    <section id="contacto" class="contacto container section-text">
      <h2>📩 Contacto</h2>
      <form class="form-contacto" action="https://formsubmit.co/samaosempires@gmail.com" method="POST">
        <input type="hidden" name="_captcha" value="false" />
        <label>Tu nombre</label><input type="text" name="nombre" required />
        <label>Tu email</label><input type="email" name="email" required />
        <label>Tu mensaje</label><textarea name="mensaje" rows="5" required></textarea>
        <button type="submit" class="boton">Enviar mensaje</button>
      </form>
    </section>
  </main>

  <footer role="contentinfo" class="footer">
    <div class="container footer-content">
      <p>&copy; 2025 Cooperativa de Viviendas - Todos los derechos reservados.</p>
    </div>
  </footer>

  <script>
    const items = document.querySelectorAll('.section-text, .item, .imagenes, .contacto, .ubicacion');
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if(entry.isIntersecting) entry.target.classList.add('visible');
      });
    }, { threshold: 0.2 });
    items.forEach(item => observer.observe(item));

    const hamburger = document.querySelector('.hamburger');
const menu = document.querySelector('.menu');
const closeMenu = document.querySelector('.hamburger-close');

hamburger.addEventListener('click', () => {
  hamburger.classList.toggle('active');
  menu.classList.toggle('active');
  const expanded = hamburger.getAttribute('aria-expanded') === 'true' || false;
  hamburger.setAttribute('aria-expanded', !expanded);
});

closeMenu.addEventListener('click', () => {
  hamburger.classList.remove('active');
  menu.classList.remove('active');
  hamburger.setAttribute('aria-expanded', false);
});

const menuLinks = document.querySelectorAll('.menu a[href^="#"]');
menuLinks.forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    const targetId = link.getAttribute('href').substring(1);
    const targetSection = document.getElementById(targetId);
    if(targetSection){
      targetSection.scrollIntoView({ behavior: 'smooth' });
    }
    hamburger.classList.remove('active');
    menu.classList.remove('active');
    hamburger.setAttribute('aria-expanded', false);
  });
});
  </script>
</body>
</html>
