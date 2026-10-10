<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#164f3e">
  <meta name="description"
        content="Ypora: monitoreo ambiental y gestión de efluentes para establecimientos y organismos públicos. Tecnología al servicio del Iberá.">
  <title>Ypora — Tecnología para cuidar el Iberá</title>

  <style>
    /* ==================== BASE ==================== */
    :root {
      --green-950: #102f29;
      --green-900: #164f3e;
      --green-700: #287457;
      --green-500: #56a581;
      --green-200: #cde7d8;
      --green-100: #e8f3ec;
      --cream: #f7f9f5;
      --text: #173c32;
      --muted: #5f746b;
      --white: #fff;
      --border: rgba(34, 89, 65, .12);
      --shadow: 0 16px 44px rgba(22, 79, 62, .06);
      --radius: 24px;
      --container: 1180px;
    }

    *, *::before, *::after { box-sizing: border-box; }
    html {
      scroll-behavior: smooth;
      scroll-padding-top: 100px;
    }
    body {
      margin: 0;
      color: var(--text);
      background: var(--cream);
      font-family: system-ui, -apple-system, BlinkMacSystemFont,
                   "Segoe UI", sans-serif;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
    }
    a { color: inherit; text-decoration: none; }
    svg { display: block; }
    img { display: block; max-width: 100%; }
    h1, h2, h3, p { margin: 0; }
    h1, h2, h3 { line-height: 1.12; }
    ::selection { background: var(--green-200); }

    .container {
      width: min(var(--container), calc(100% - 48px));
      margin-inline: auto;
    }
    .icon {
      width: 24px;
      height: 24px;
      fill: none;
      stroke: currentColor;
      stroke-width: 1.7;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    .eyebrow {
      display: block;
      margin-bottom: 20px;
      color: var(--green-700);
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: .16em;
      text-transform: uppercase;
    }
    .button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      min-height: 52px;
      padding: 14px 24px;
      border: 1px solid transparent;
      border-radius: 14px;
      font-size: .9rem;
      font-weight: 650;
      transition: transform .25s ease, background .25s ease,
                  box-shadow .25s ease;
    }
    .button:hover { transform: translateY(-3px); }
    .button-primary {
      color: var(--white);
      background: var(--green-900);
      box-shadow: 0 8px 22px rgba(22, 79, 62, .16);
    }
    .button-primary:hover {
      background: var(--green-700);
      box-shadow: 0 12px 26px rgba(22, 79, 62, .2);
    }
    .button-outline {
      border-color: var(--border);
      background: rgba(255, 255, 255, .65);
    }
    .button-outline:hover { background: var(--green-100); }
    a:focus-visible {
      outline: 3px solid var(--green-500);
      outline-offset: 5px;
    }
    .skip-link {
      position: fixed;
      top: 12px;
      left: 12px;
      z-index: 100;
      padding: 12px 20px;
      border-radius: 10px;
      color: white;
      background: var(--green-900);
      transform: translateY(-160%);
    }
    .skip-link:focus { transform: translateY(0); }

    /* ==================== HEADER ==================== */
    .header {
      position: fixed;
      inset: 0 0 auto;
      z-index: 20;
      border-bottom: 1px solid var(--border);
      background: rgba(247, 249, 245, .92);
    }
    @supports (backdrop-filter: blur(18px)) {
      .header {
        background: rgba(247, 249, 245, .78);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
      }
    }
    .nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
      min-height: 84px;
    }
    .brand {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-size: 1.65rem;
      font-weight: 750;
      letter-spacing: -.06em;
    }
    .brand-mark {
      display: grid;
      place-items: center;
      width: 38px;
      height: 44px;
      color: var(--green-700);
    }
    .brand-mark .icon { width: 34px; height: 40px; }
    .nav-links {
      display: flex;
      align-items: center;
      gap: 30px;
      color: var(--muted);
      font-size: .86rem;
    }
    .nav-links a:hover { color: var(--green-900); }
    .nav .button { min-height: 44px; padding: 10px 18px; }

    /* ==================== HERO ==================== */
    .hero {
      position: relative;
      overflow: hidden;
      padding: 158px 0 68px;
      background:
        radial-gradient(ellipse at 5% 35%, #e4f0e7 0, transparent 48%),
        radial-gradient(ellipse at 95% 15%, #edf4e4 0, transparent 42%);
    }
    .hero-grid {
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: 1.02fr 1fr;
      align-items: center;
      gap: 68px;
    }
    .hero h1 {
      max-width: 620px;
      font-size: clamp(2.65rem, 4.7vw, 4.6rem);
      font-weight: 650;
      letter-spacing: -.065em;
    }
    .hero h1 span { color: var(--green-700); }
    .hero-description {
      max-width: 490px;
      margin-top: 28px;
      color: var(--muted);
      font-size: 1.05rem;
      line-height: 1.8;
    }
    .hero-actions {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 16px;
      margin-top: 32px;
    }
    .text-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 4px;
      font-size: .88rem;
      font-weight: 600;
    }
    .hero-note {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 26px;
      color: var(--muted);
      font-size: .76rem;
    }
    .hero-note .icon { width: 17px; height: 17px; }

    .hero-visual { position: relative; padding-bottom: 24px; }
    .hero-image {
      position: relative;
      display: grid;
      place-items: center;
      min-height: 510px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, .7);
      border-radius: 110px 24px 24px 24px;
      color: var(--green-900);
      background:
        radial-gradient(ellipse at 80% 12%, #fff4ca 0, transparent 38%),
        linear-gradient(165deg, #dcece3, #adcfc0 48%, #73a991);
      box-shadow: 0 24px 70px rgba(22, 79, 62, .12);
    }
    /* Sustituir el placeholder por:
       <img class="photo" src="/images/ibera-hero.jpg"
            alt="Vista panorámica de los Esteros del Iberá">
    */
    .photo {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .image-placeholder {
      position: relative;
      z-index: 1;
      display: grid;
      justify-items: center;
      gap: 10px;
      padding: 24px;
      text-align: center;
    }
    .image-placeholder .icon { width: 40px; height: 40px; }
    .image-placeholder strong { font-size: 1rem; font-weight: 600; }
    .image-placeholder small { font-size: .75rem; opacity: .75; }

    .location-label {
      position: absolute;
      top: 24px;
      right: 22px;
      z-index: 2;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 8px 12px;
      border: 1px solid rgba(255, 255, 255, .6);
      border-radius: 100px;
      background: rgba(255, 255, 255, .75);
      font-size: .7rem;
      font-weight: 600;
    }
    .location-label .icon { width: 15px; height: 15px; }
    .visual-caption {
      position: absolute;
      right: 24px;
      bottom: 24px;
      z-index: 2;
      padding: 8px 12px;
      border-radius: 8px;
      background: rgba(255, 255, 255, .8);
      font-size: .7rem;
    }
    .floating-card {
      position: absolute;
      left: -28px;
      bottom: 0;
      z-index: 3;
      display: flex;
      align-items: center;
      gap: 14px;
      max-width: calc(100% - 24px);
      padding: 20px 24px;
      border: 1px solid rgba(255, 255, 255, .85);
      border-radius: 20px;
      background: rgba(255, 255, 255, .94);
      box-shadow: var(--shadow);
    }
    .floating-card > .icon {
      width: 42px;
      height: 42px;
      padding: 10px;
      border-radius: 50%;
      color: var(--green-700);
      background: var(--green-100);
    }
    .floating-card strong { display: block; font-size: .88rem; }
    .floating-card span {
      display: block;
      margin-top: 2px;
      color: var(--muted);
      font-size: .73rem;
    }

    .ambient {
      position: absolute;
      pointer-events: none;
      border-radius: 65% 35% 70% 30%;
      background: rgba(111, 166, 130, .13);
      filter: blur(5px);
      animation: drift 16s ease-in-out infinite alternate;
    }
    .ambient-one {
      top: 140px;
      left: -60px;
      width: 180px;
      height: 250px;
    }
    .ambient-two {
      bottom: 10px;
      left: 44%;
      width: 90px;
      height: 130px;
      animation-duration: 20s;
      animation-delay: -8s;
    }
    @keyframes drift {
      from { transform: translate3d(0, 0, 0) rotate(-15deg); }
      to { transform: translate3d(24px, -36px, 0) rotate(12deg); }
    }

    /* ==================== CONTEXTO ==================== */
    .context {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
      padding: 28px 0;
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }
    .context p { color: var(--muted); font-size: .85rem; }
    .context-tags { display: flex; flex-wrap: wrap; gap: 12px; }
    .context-tags span {
      padding: 7px 14px;
      border-radius: 100px;
      background: var(--green-100);
      font-size: .75rem;
      font-weight: 600;
    }

    /* ==================== CÓMO FUNCIONA ==================== */
    .how { padding: 100px 0 108px; }
    .section-heading {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 40px;
      margin-bottom: 42px;
    }
    .section-heading h2,
    .conservation h2 {
      font-size: clamp(2rem, 3.5vw, 3.25rem);
      font-weight: 600;
      letter-spacing: -.05em;
    }
    .section-heading > p {
      max-width: 350px;
      color: var(--muted);
      font-size: .93rem;
    }
    .cards {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 18px;
    }
    .card {
      position: relative;
      padding: 28px 24px 32px;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: rgba(255, 255, 255, .78);
      box-shadow: var(--shadow);
      transition: transform .3s ease, box-shadow .3s ease,
                  border-color .3s ease;
    }
    .card:hover {
      transform: translateY(-7px);
      border-color: rgba(40, 116, 87, .3);
      box-shadow: 0 22px 45px rgba(22, 79, 62, .1);
    }
    .card-icon {
      display: grid;
      place-items: center;
      width: 48px;
      height: 48px;
      margin-bottom: 30px;
      border-radius: 15px;
      color: var(--green-700);
      background: var(--green-100);
    }
    .card-number {
      position: absolute;
      top: 32px;
      right: 24px;
      color: var(--muted);
      font-size: .7rem;
    }
    .card h3 {
      margin-bottom: 14px;
      font-size: 1.02rem;
      line-height: 1.35;
      letter-spacing: -.02em;
    }
    .card p {
      color: var(--muted);
      font-size: .85rem;
      line-height: 1.75;
    }

    /* ==================== CONSERVACIÓN ==================== */
    .conservation {
      overflow: hidden;
      padding: 86px 0;
      color: var(--white);
      background:
        radial-gradient(ellipse at 0 100%, rgba(86, 165, 129, .2),
                        transparent 60%),
        var(--green-950);
    }
    .conservation-grid {
      display: grid;
      grid-template-columns: .85fr 1.15fr;
      align-items: center;
      gap: 72px;
    }
    .conservation .eyebrow { color: #a6d9ba; }
    .conservation h2 { max-width: 390px; }
    .conservation-copy > p {
      margin-top: 24px;
      color: #c4d8ce;
      font-size: .97rem;
      line-height: 1.85;
    }
    .conservation-quote {
      margin-top: 32px;
      padding-left: 18px;
      border-left: 2px solid #80bc99;
      color: #e2efe6;
      font-size: 1.1rem;
    }
    .gallery {
      display: grid;
      grid-template-columns: 1.05fr .95fr;
      grid-template-rows: 210px 210px;
      gap: 16px;
    }
    .gallery-item {
      position: relative;
      display: grid;
      place-items: center;
      overflow: hidden;
      margin: 0;
      border: 1px solid rgba(255, 255, 255, .14);
      border-radius: 22px;
      color: #e2efe6;
      background: linear-gradient(145deg, #356e58, #21483d);
    }
    .gallery-item:first-child { grid-row: 1 / 3; }
    .gallery-item:nth-child(2) {
      background: linear-gradient(145deg, #517157, #2b5242);
    }
    .gallery-item:nth-child(3) {
      background: linear-gradient(145deg, #496f65, #233f37);
    }
    .gallery-item figcaption {
      position: absolute;
      inset: auto 0 0;
      z-index: 2;
      padding: 40px 18px 18px;
      background: linear-gradient(transparent, rgba(9, 31, 25, .8));
      font-size: .78rem;
    }

    /* ==================== CIERRE ==================== */
    .closing { padding: 80px 0; text-align: center; }
    .closing h2 {
      font-size: clamp(1.8rem, 3vw, 2.5rem);
      font-weight: 600;
      letter-spacing: -.045em;
    }
    .closing p {
      max-width: 560px;
      margin: 18px auto 26px;
      color: var(--muted);
      font-size: .95rem;
    }
    .footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
      padding: 26px 0;
      border-top: 1px solid var(--border);
    }
    .footer .brand { font-size: 1.3rem; }
    .footer p { color: var(--muted); font-size: .75rem; }

    /* ==================== RESPONSIVE ==================== */
    @media (max-width: 1050px) {
      .hero-grid { gap: 40px; }
      .hero-image { min-height: 460px; }
      .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .conservation-grid { gap: 36px; }
    }
    @media (max-width: 760px) {
      .container { width: calc(100% - 40px); }
      .nav { min-height: 74px; gap: 12px; }
      .nav-links { display: none; }
      .brand { font-size: 1.45rem; }
      .hero { padding: 120px 0 44px; }
      .hero-grid, .conservation-grid { grid-template-columns: 1fr; }
      .hero-grid { gap: 44px; }
      .hero h1 { max-width: 560px; }
      .hero-description { max-width: none; font-size: 1rem; }
      .hero-image {
        min-height: 410px;
        border-top-left-radius: 80px;
      }
      .floating-card { left: 14px; padding: 16px 18px; }
      .context { align-items: flex-start; flex-direction: column; gap: 14px; }
      .how { padding: 64px 0; }
      .section-heading { align-items: flex-start; flex-direction: column; gap: 20px; }
      .section-heading > p { max-width: 520px; }
      .conservation { padding: 64px 0; }
      .conservation h2 { max-width: 500px; }
      .conservation-grid { gap: 36px; }
      .gallery { grid-template-rows: 180px 180px; }
      .closing { padding: 60px 0; }
      .footer { align-items: flex-start; flex-direction: column; gap: 12px; }
    }
    @media (max-width: 440px) {
      .container { width: calc(100% - 32px); }
      .nav .button { padding: 10px 12px; font-size: .78rem; }
      .hero h1 { font-size: 2.65rem; }
      .hero-actions { align-items: stretch; flex-direction: column; gap: 6px; }
      .text-link { justify-content: center; }
      .hero-image { min-height: 350px; }
      .cards { grid-template-columns: 1fr; }
      .card { padding: 24px; }
      .card-icon { margin-bottom: 22px; }
      .gallery { gap: 10px; grid-template-rows: 155px 155px; }
      .gallery-item { border-radius: 16px; }
      .gallery .image-placeholder { padding: 12px; }
      .gallery .image-placeholder strong { font-size: .8rem; }
      .gallery .image-placeholder small { font-size: .65rem; }
    }

    /* Accesibilidad: respetar la preferencia de movimiento reducido. */
    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
      *, *::before, *::after {
        animation: none !important;
        transition: none !important;
      }
      .card:hover, .button:hover { transform: none; }
    }
  </style>
</head>

<body>
  <!-- Iconos compartidos: SVG inline, sin librerías externas. -->
  <svg xmlns="http://www.w3.org/2000/svg"
       width="0" height="0"
       aria-hidden="true"
       style="position:absolute;overflow:hidden">
    <defs>
      <symbol id="i-drop" viewBox="0 0 24 24">
        <path d="M12 2S4.5 10 4.5 15a7.5 7.5 0 0 0 15 0C19.5 10 12 2 12 2Z"/>
        <path d="M12 8s-3.5 4.2-3.5 7a3.5 3.5 0 0 0 7 0"/>
      </symbol>
      <symbol id="i-arrow" viewBox="0 0 24 24">
        <path d="M5 12h14m-6-6 6 6-6 6"/>
      </symbol>
      <symbol id="i-trace" viewBox="0 0 24 24">
        <rect x="5" y="4" width="14" height="17" rx="3"/>
        <path d="M9 4V2h6v2M9 10h6M9 14h6M9 18h3"/>
      </symbol>
      <symbol id="i-monitor" viewBox="0 0 24 24">
        <path d="M2 12h4l3-7 6 14 3-7h4"/>
      </symbol>
      <symbol id="i-weather" viewBox="0 0 24 24">
        <path d="M6 15a4 4 0 1 1 0-8 6 6 0 0 1 11-1 4.5 4.5 0 0 1 1 9"/>
        <path d="m9 18-1 3m5-3-1 3m5-3-1 3"/>
      </symbol>
      <symbol id="i-shield" viewBox="0 0 24 24">
        <path d="m12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6l8-4Z"/>
        <path d="m8 12 3 3 5-6"/>
      </symbol>
      <symbol id="i-pin" viewBox="0 0 24 24">
        <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/>
        <circle cx="12" cy="10" r="2.5"/>
      </symbol>
      <symbol id="i-landscape" viewBox="0 0 24 24">
        <rect x="2" y="3" width="20" height="18" rx="3"/>
        <circle cx="16" cy="8" r="2"/>
        <path d="m2 17 6-7 6 7 3-3 5 5"/>
      </symbol>
      <symbol id="i-leaf" viewBox="0 0 24 24">
        <path d="M20 3C9 2 3 6 4 13c1 7 11 9 15 1 2-4 1-11 1-11Z"/>
        <path d="M4 21 15 10"/>
      </symbol>
    </defs>
  </svg>

  <a class="skip-link" href="#contenido">Saltar al contenido</a>

  <header class="header">
    <nav class="nav container" aria-label="Navegación principal">
      <a class="brand" href="#inicio" aria-label="Ypora, inicio">
        <span class="brand-mark">
          <svg class="icon" aria-hidden="true"><use href="#i-drop"/></svg>
        </span>
        Ypora
      </a>

      <div class="nav-links">
        <a href="#como-funciona">Cómo funciona</a>
        <a href="#ibera">Nuestro compromiso</a>
      </div>

      <a class="button button-outline" href="/login">
        Iniciar Sesión
        <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>
      </a>
    </nav>
  </header>

  <main id="contenido">
    <section class="hero" id="inicio" aria-labelledby="hero-title">
      <div class="ambient ambient-one" aria-hidden="true"></div>
      <div class="ambient ambient-two" aria-hidden="true"></div>

      <div class="hero-grid container">
        <div>
          <span class="eyebrow">Tecnología al servicio de la naturaleza</span>
          <h1 id="hero-title">
            Cuidar el agua.<br>
            <span>Proteger la vida.</span>
          </h1>
          <p class="hero-description">
            Monitoreo ambiental y gestión de efluentes para
            establecimientos y organismos públicos. Información conectada
            para acompañar el cuidado de los Esteros del Iberá.
          </p>

          <div class="hero-actions">
            <a class="button button-primary" href="/login">
              Ingresar al Sistema
              <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>
            </a>
            <a class="text-link" href="#como-funciona">
              Conocé cómo funciona
              <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>
            </a>
          </div>

          <p class="hero-note">
            <svg class="icon" aria-hidden="true"><use href="#i-leaf"/></svg>
            Un compromiso compartido con el futuro de Corrientes.
          </p>
        </div>

        <div class="hero-visual">
          <div class="hero-image">
            <!-- Reemplazar este bloque por una fotografía .photo. -->
            <div class="image-placeholder">
              <svg class="icon" aria-hidden="true">
                <use href="#i-landscape"/>
              </svg>
              <strong>Esteros del Iberá</strong>
              <small>Espacio para una fotografía panorámica</small>
            </div>

            <span class="location-label">
              <svg class="icon" aria-hidden="true"><use href="#i-pin"/></svg>
              Corrientes, Argentina
            </span>
            <span class="visual-caption">Agua, biodiversidad y futuro.</span>
          </div>

          <div class="floating-card">
            <svg class="icon" aria-hidden="true"><use href="#i-drop"/></svg>
            <div>
              <strong>Más información. Mejor cuidado.</strong>
              <span>Gestión ambiental con una mirada integral.</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="container">
      <div class="context">
        <p>Una misma herramienta. Un compromiso compartido.</p>
        <div class="context-tags" aria-label="Destinatarios de Ypora">
          <span>Establecimientos</span>
          <span>Organismos públicos</span>
          <span>Ecosistema Iberá</span>
        </div>
      </div>
    </div>

    <section class="how container"
             id="como-funciona"
             aria-labelledby="how-title">
      <div class="section-heading">
        <div>
          <span class="eyebrow">Cómo funciona</span>
          <h2 id="how-title">Cada dato cuenta.<br>Cada acción también.</h2>
        </div>
        <p>
          Registrá, consultá y acompañá la gestión ambiental
          desde un mismo lugar.
        </p>
      </div>

      <div class="cards">
        <article class="card">
          <span class="card-number" aria-hidden="true">01</span>
          <div class="card-icon">
            <svg class="icon" aria-hidden="true"><use href="#i-trace"/></svg>
          </div>
          <h3>Trazabilidad</h3>
          <p>
            Organizá los análisis de efluentes y consultá el historial
            de cada establecimiento para seguir su evolución.
          </p>
        </article>

        <article class="card">
          <span class="card-number" aria-hidden="true">02</span>
          <div class="card-icon">
            <svg class="icon" aria-hidden="true"><use href="#i-monitor"/></svg>
          </div>
          <h3>Monitoreo en tiempo real</h3>
          <p>
            Consultá la información disponible y detectá cambios
            relevantes para orientar decisiones a tiempo.
          </p>
        </article>

        <article class="card">
          <span class="card-number" aria-hidden="true">03</span>
          <div class="card-icon">
            <svg class="icon" aria-hidden="true"><use href="#i-weather"/></svg>
          </div>
          <h3>Alertas climáticas</h3>
          <p>
            Anticipá situaciones que pueden afectar la gestión
            de efluentes y prepará medidas preventivas.
          </p>
        </article>

        <article class="card">
          <span class="card-number" aria-hidden="true">04</span>
          <div class="card-icon">
            <svg class="icon" aria-hidden="true"><use href="#i-shield"/></svg>
          </div>
          <h3>Cumplimiento normativo</h3>
          <p>
            Seguí los resultados de los análisis y los vencimientos
            para acompañar tus obligaciones ambientales.
          </p>
        </article>
      </div>
    </section>

    <section class="conservation" id="ibera"
             aria-labelledby="ibera-title">
      <div class="conservation-grid container">
        <div class="conservation-copy">
          <span class="eyebrow">Nuestro compromiso</span>
          <h2 id="ibera-title">Cuidemos juntos el Iberá.</h2>
          <p>
            El agua conecta todo: los paisajes, la biodiversidad
            y las comunidades que habitan este territorio.
            Protegerla es cuidar mucho más que un recurso.
          </p>
          <p>
            Ypora busca acercar información a quienes pueden actuar.
            Porque una gestión responsable de los efluentes
            también es una forma de conservar la vida.
          </p>
          <div class="conservation-quote">
            El agua también es parte de nuestro futuro.
          </div>
        </div>

        <div class="gallery" aria-label="Paisajes y biodiversidad del Iberá">
          <figure class="gallery-item">
            <!-- Reemplazar el placeholder por:
            <img class="photo" src="/images/ibera-humedal.jpg"
                 alt="Vegetación y canales de agua de los Esteros del Iberá">
            -->
            <div class="image-placeholder">
              <svg class="icon" aria-hidden="true">
                <use href="#i-landscape"/>
              </svg>
              <strong>El humedal</strong>
              <small>Espacio para fotografía</small>
            </div>
            <figcaption>Un paisaje que merece ser cuidado.</figcaption>
          </figure>

          <figure class="gallery-item">
            <!-- Imagen sugerida: fauna autóctona del Iberá. -->
            <div class="image-placeholder">
              <svg class="icon" aria-hidden="true">
                <use href="#i-landscape"/>
              </svg>
              <strong>Fauna del Iberá</strong>
              <small>Espacio para fotografía</small>
            </div>
            <figcaption>La vida que nos rodea.</figcaption>
          </figure>

          <figure class="gallery-item">
            <!-- Imagen sugerida: flora y vegetación acuática. -->
            <div class="image-placeholder">
              <svg class="icon" aria-hidden="true"><use href="#i-leaf"/></svg>
              <strong>Flora nativa</strong>
              <small>Espacio para fotografía</small>
            </div>
            <figcaption>Un equilibrio que compartimos.</figcaption>
          </figure>
        </div>
      </div>
    </section>

    <section class="closing container" aria-labelledby="closing-title">
      <span class="eyebrow">Empecemos por lo que podemos cuidar</span>
      <h2 id="closing-title">Una gestión más clara.<br>Un futuro más verde.</h2>
      <p>
        Accedé a Ypora y acompañá el cuidado del agua
        desde tu establecimiento u organismo.
      </p>
      <a class="button button-primary" href="/login">
        Ingresar al Sistema
        <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg>
      </a>
    </section>
  </main>

  <footer class="footer container">
    <a class="brand" href="#inicio" aria-label="Ypora, volver al inicio">
      <span class="brand-mark">
        <svg class="icon" aria-hidden="true"><use href="#i-drop"/></svg>
      </span>
      Ypora
    </a>
    <p>Monitoreo ambiental y gestión de efluentes · Corrientes, Argentina</p>
  </footer>
</body>
</html>