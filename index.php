<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogiTrack — Soluciones de Logística</title>
    <link rel="stylesheet" href="/assets/estilo.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: var(--cream); }

        /* ===== NAV ===== */
        .landing-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 16px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .landing-nav .brand {
            font-family: 'DM Serif Display', serif;
            font-size: 22px;
            color: var(--rose-dark);
        }
        .landing-nav .nav-links { display: flex; gap: 8px; align-items: center; }
        .landing-nav .nav-links a {
            color: var(--text-soft);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background 0.2s, color 0.2s;
        }
        .landing-nav .nav-links a:hover { background: var(--nude); color: var(--rose-dark); }
        .landing-nav .btn-nav {
            background: var(--rose-dark);
            color: white !important;
            font-weight: 600 !important;
            border-radius: 8px;
        }
        .landing-nav .btn-nav:hover { background: #4338ca !important; }

        /* ===== HERO ===== */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 60px 80px;
            text-align: center;
            background: linear-gradient(160deg, #f5f6ff 0%, #eef2ff 60%, #f5f6ff 100%);
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -200px; right: -200px;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(79,70,229,0.10) 0%, transparent 70%);
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -150px; left: -150px;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(129,140,248,0.08) 0%, transparent 70%);
        }
        .hero-content { position: relative; z-index: 1; max-width: 680px; }
        .hero-tag {
            display: inline-block;
            background: var(--nude-dark);
            color: var(--rose-dark);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 20px;
            margin-bottom: 24px;
        }
        .hero h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 58px;
            line-height: 1.1;
            color: var(--text);
            margin-bottom: 20px;
        }
        .hero h1 span { color: var(--rose-dark); }
        .hero p {
            font-size: 18px;
            color: var(--text-soft);
            line-height: 1.7;
            margin-bottom: 40px;
            max-width: 520px;
            margin-left: auto;
            margin-right: auto;
        }
        .hero-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .hero-btn-primary {
            background: var(--rose-dark);
            color: white;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: background 0.2s, transform 0.1s;
        }
        .hero-btn-primary:hover { background: #4338ca; transform: translateY(-2px); }
        .hero-btn-secondary {
            background: white;
            color: var(--rose-dark);
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            border: 1.5px solid var(--border);
            transition: background 0.2s;
        }
        .hero-btn-secondary:hover { background: var(--nude); }

        /* ===== STATS ===== */
        .stats {
            display: flex;
            justify-content: center;
            gap: 0;
            background: white;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 40px 60px;
        }
        .stat-item {
            flex: 1;
            text-align: center;
            padding: 0 40px;
            border-right: 1px solid var(--border);
        }
        .stat-item:last-child { border-right: none; }
        .stat-item .num {
            font-family: 'DM Serif Display', serif;
            font-size: 40px;
            color: var(--rose-dark);
            line-height: 1;
            margin-bottom: 6px;
        }
        .stat-item .desc { color: var(--text-soft); font-size: 13px; }

        /* ===== SERVICIOS ===== */
        .section {
            padding: 80px 60px;
            max-width: 1100px;
            margin: 0 auto;
        }
        .section-header { text-align: center; margin-bottom: 52px; }
        .section-header h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 38px;
            color: var(--text);
            margin-bottom: 10px;
        }
        .section-header p { color: var(--text-soft); font-size: 15px; max-width: 480px; margin: 0 auto; }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .service-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px 24px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .service-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(79,70,229,0.12); }
        .service-icon {
            width: 44px; height: 44px;
            background: var(--nude);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        .service-icon i { color: var(--rose-dark); font-size: 20px; }
        .cta-btn { border-radius: 8px; }
        .service-card h3 { font-size: 17px; font-weight: 600; margin-bottom: 8px; color: var(--text); }
        .service-card p { font-size: 14px; color: var(--text-soft); line-height: 1.6; }

        /* ===== CTA ===== */
        .cta-section {
            background: linear-gradient(135deg, #1e1b4b, #4f46e5);
            padding: 80px 60px;
            text-align: center;
            color: white;
        }
        .cta-section h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 38px;
            margin-bottom: 14px;
        }
        .cta-section p { font-size: 16px; opacity: 0.88; margin-bottom: 36px; }
        .cta-btn {
            display: inline-block;
            background: white;
            color: var(--rose-dark);
            padding: 14px 36px;
            border-radius: 14px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 700;
            transition: transform 0.2s;
        }
        .cta-btn:hover { transform: translateY(-2px); }

        /* ===== FOOTER ===== */
        footer {
            background: var(--text);
            color: rgba(255,255,255,0.5);
            text-align: center;
            padding: 28px 60px;
            font-size: 13px;
        }
        footer span { color: var(--rose); }
    </style>
</head>
<body>

<!-- NAV -->
<nav class="landing-nav">
    <div class="brand">LogiTrack</div>
    <div class="nav-links">
        <a href="/rastrear.php">Rastrear pedido</a>
        <a href="#servicios">Servicios</a>
        <a href="#nosotros">Nosotros</a>
        <a href="/cliente/registro.php">Registrarse</a>
        <a href="/cliente/login.php" class="btn-nav">Ingresar</a>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-tag">Sistema de logística integral</div>
        <h1>Tu envío,<br><span>siempre en control</span></h1>
        <p>LogiTrack te permite gestionar, rastrear y coordinar tus envíos de manera simple, segura y en tiempo real.</p>
        <div class="hero-btns">
            <a href="/cliente/login.php" class="hero-btn-primary">Ingresar al sistema</a>
            <a href="/cliente/registro.php" class="hero-btn-secondary">Crear cuenta de cliente</a>
        </div>
    </div>
</section>

<!-- STATS -->
<div class="stats">
    <div class="stat-item">
        <div class="num">+500</div>
        <div class="desc">Envíos procesados</div>
    </div>
    <div class="stat-item">
        <div class="num">4</div>
        <div class="desc">Sucursales activas</div>
    </div>
    <div class="stat-item">
        <div class="num">24h</div>
        <div class="desc">Seguimiento en tiempo real</div>
    </div>
    <div class="stat-item">
        <div class="num">100%</div>
        <div class="desc">Acceso seguro por rol</div>
    </div>
</div>

<!-- SERVICIOS -->
<div id="servicios">
    <div class="section">
        <div class="section-header">
            <h2>¿Qué ofrecemos?</h2>
            <p>Soluciones completas para la gestión y seguimiento de envíos</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon"><i class="fa-solid fa-box"></i></div>
                <h3>Gestión de envíos</h3>
                <p>Creá, modificá y administrá envíos desde un panel centralizado con toda la información necesaria.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                <h3>Rastreo en tiempo real</h3>
                <p>Seguí el estado de tu paquete en cada etapa, desde la sucursal de origen hasta la entrega final.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fa-solid fa-route"></i></div>
                <h3>Coordinación de rutas</h3>
                <p>Asignación eficiente de viajes y choferes para garantizar entregas puntuales y organizadas.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>Acceso por roles</h3>
                <p>Cada usuario accede solo a lo que necesita: administrador, empleado, chofer o cliente.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fa-solid fa-chart-bar"></i></div>
                <h3>Reportes y estadísticas</h3>
                <p>Visualizá el rendimiento del sistema con reportes detallados sobre envíos y operaciones.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><i class="fa-solid fa-building"></i></div>
                <h3>Red de sucursales</h3>
                <p>Operamos desde múltiples sucursales para llegar a más destinos con mayor eficiencia.</p>
            </div>
        </div>
    </div>
</div>

<!-- NOSOTROS -->
<div id="nosotros" style="background: white; border-top: 1px solid var(--border);">
    <div class="section" style="display:flex; gap:60px; align-items:center;">
        <div style="flex:1;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--rose-dark);margin-bottom:14px;">Nuestra empresa</div>
            <h2 style="font-family:'DM Serif Display',serif;font-size:36px;color:var(--text);margin-bottom:16px;line-height:1.2;">Logística con propósito y precisión</h2>
            <p style="color:var(--text-soft);font-size:15px;line-height:1.8;margin-bottom:20px;">LogiTrack nació para simplificar la logística de envíos, conectando remitentes, destinatarios y operadores en una sola plataforma clara y eficiente.</p>
            <p style="color:var(--text-soft);font-size:15px;line-height:1.8;">Contamos con un equipo de choferes, empleados y sucursales distribuidas para garantizar que cada paquete llegue a destino en tiempo y forma.</p>
        </div>
        <div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div style="background:var(--nude);border-radius:16px;padding:24px;text-align:center;">
                <div style="font-family:'DM Serif Display',serif;font-size:32px;color:var(--rose-dark);">2024</div>
                <div style="font-size:13px;color:var(--text-soft);margin-top:4px;">Año de fundación</div>
            </div>
            <div style="background:var(--nude);border-radius:16px;padding:24px;text-align:center;">
                <div style="font-family:'DM Serif Display',serif;font-size:32px;color:var(--rose-dark);">4</div>
                <div style="font-size:13px;color:var(--text-soft);margin-top:4px;">Sucursales</div>
            </div>
            <div style="background:var(--nude);border-radius:16px;padding:24px;text-align:center;">
                <div style="font-family:'DM Serif Display',serif;font-size:32px;color:var(--rose-dark);">24/7</div>
                <div style="font-size:13px;color:var(--text-soft);margin-top:4px;">Seguimiento online</div>
            </div>
            <div style="background:var(--nude);border-radius:16px;padding:24px;text-align:center;">
                <div style="font-family:'DM Serif Display',serif;font-size:32px;color:var(--rose-dark);">100%</div>
                <div style="font-size:13px;color:var(--text-soft);margin-top:4px;">Sistema seguro</div>
            </div>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="cta-section">
    <h2>¿Lista para empezar?</h2>
    <p>Creá tu cuenta de cliente y empezá a gestionar tus envíos hoy mismo.</p>
    <a href="/cliente/registro.php" class="cta-btn">Crear cuenta gratis</a>
</div>

<!-- FOOTER -->
<footer>
    © 2024 <span>LogiTrack</span> — Sistema de gestión de envíos. Todos los derechos reservados.
</footer>

</body>
</html>