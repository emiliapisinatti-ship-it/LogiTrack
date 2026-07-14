<?php
$page_subtitle = 'Mi cuenta';
$nav_links = [];
$extra_css = '
body { background: #f1f5f9; }
.lt-container { max-width: 100%; margin: 0; padding: 0; }
.panel-wrap { display: flex; min-height: calc(100vh - 56px); }

.p-sidebar {
    width: 220px; flex-shrink: 0;
    background: #1e1b4b; padding: 28px 0 20px;
    display: flex; flex-direction: column; gap: 2px;
    position: sticky; top: 56px;
    height: calc(100vh - 56px); overflow-y: auto;
}
.p-sidebar-label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.2px; color: #6366f1; padding: 0 20px 8px; margin-top: 4px;
}
.p-sidebar a {
    display: flex; align-items: center; gap: 11px;
    padding: 10px 20px; color: #c7d2fe; text-decoration: none;
    font-size: 14px; font-weight: 500;
    border-left: 3px solid transparent;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.p-sidebar a:hover { background: rgba(99,102,241,0.18); color: #fff; border-left-color: #818cf8; }
.p-sidebar a i { width: 17px; text-align: center; font-size: 14px; opacity: 0.85; }

.p-main { flex: 1; padding: 32px 36px; min-width: 0; }
.p-greeting { margin-bottom: 28px; }
.p-greeting h1 { font-size: 24px; font-weight: 700; color: #1e1b3a; margin-bottom: 4px; font-family: "DM Serif Display", serif; }
.p-greeting p { font-size: 14px; color: #64748b; }

.menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
.menu-item { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; text-decoration: none; color: #1e1b3a; transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s; }
.menu-item:hover { border-color: #6366f1; box-shadow: 0 4px 16px rgba(99,102,241,0.12); transform: translateY(-2px); }
.menu-item-icon { width: 40px; height: 40px; background: #eff6ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.menu-item-icon i { color: #4f46e5; font-size: 17px; }
.menu-item-text h3 { font-size: 14px; font-weight: 600; margin-bottom: 3px; }
.menu-item-text p  { font-size: 12px; color: #94a3b8; line-height: 1.3; }

.stat-card { flex:1; min-width:130px; border-radius:12px; padding:18px 22px; text-decoration:none; display:block; transition:box-shadow 0.2s, transform 0.15s; }
.stat-card:hover { box-shadow:0 6px 20px rgba(0,0,0,0.10); transform:translateY(-2px); }
.stat-card-val { font-size:28px; font-weight:700; line-height:1; }
.stat-card-lbl { font-size:12px; margin-top:3px; }
.stat-white  { background:#fff; border:1px solid #e2e8f0; }
.stat-white  .stat-card-val { color:#1e1b3a; }
.stat-white  .stat-card-lbl { color:#94a3b8; }
.stat-blue   { background:#eff6ff; border:1px solid #93c5fd; }
.stat-blue   .stat-card-val { color:#1d4ed8; }
.stat-blue   .stat-card-lbl { color:#3b82f6; }
.stat-green  { background:#f0fdf4; border:1px solid #86efac; }
.stat-green  .stat-card-val { color:#15803d; }
.stat-green  .stat-card-lbl { color:#22c55e; }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
<div class="panel-wrap">

    <aside class="p-sidebar">
        <div class="p-sidebar-label">Mi cuenta</div>
        <a href="/cliente/router.php?pagina=enviar"><i class="fa-solid fa-box-open"></i> Enviar paquete</a>
        <a href="/cliente/router.php?pagina=rastrear"><i class="fa-solid fa-magnifying-glass"></i> Rastrear pedido</a>
        <a href="/cliente/router.php?pagina=mis_envios"><i class="fa-solid fa-list"></i> Mis envíos</a>
        <a href="/cliente/router.php?pagina=perfil"><i class="fa-solid fa-user"></i> Mi perfil</a>
    </aside>

    <main class="p-main">
        <div class="p-greeting">
            <h1>Bienvenido, <?= htmlspecialchars($_SESSION['username'] ?? '') ?></h1>
            <p>Panel de cliente</p>
        </div>

        <!-- Resumen -->
        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:28px;">
            <a href="/cliente/router.php?pagina=mis_envios" class="stat-card stat-white">
                <div class="stat-card-val"><?= $cli_total ?></div>
                <div class="stat-card-lbl">Total de envíos</div>
            </a>
            <a href="/cliente/router.php?pagina=mis_envios&estado=en_curso" class="stat-card stat-blue">
                <div class="stat-card-val"><?= $cli_activos ?></div>
                <div class="stat-card-lbl">En curso</div>
            </a>
            <a href="/cliente/router.php?pagina=mis_envios&estado=entregado" class="stat-card stat-green">
                <div class="stat-card-val"><?= $cli_entregados ?></div>
                <div class="stat-card-lbl">Entregados</div>
            </a>
        </div>

        <!-- Últimos envíos -->
        <?php if (!empty($cli_recientes)): ?>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
            <div style="padding:13px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:8px;">
                <i class="fa-solid fa-clock-rotate-left" style="color:#6366f1;font-size:13px;"></i>
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;color:#475569;">Últimos envíos</span>
            </div>
            <?php foreach ($cli_recientes as $env): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid #f8fafc;">
                <div>
                    <div style="font-size:13px;font-weight:600;color:#1e1b3a;font-family:monospace;"><?= htmlspecialchars($env['nro_tracking']) ?></div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">Destino: <?= htmlspecialchars($env['suc_destino'] ?? '—') ?></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:12px;font-weight:600;color:#4f46e5;"><?= htmlspecialchars($env['estado_actual'] ?? 'Sin estado') ?></div>
                    <div style="font-size:11px;color:#94a3b8;"><?= $env['fecha_creacion'] ? date('d/m/Y', strtotime($env['fecha_creacion'])) : '—' ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <div style="padding:10px 16px;">
                <a href="/cliente/router.php?pagina=mis_envios" style="font-size:12px;color:#6366f1;text-decoration:none;font-weight:600;">Ver todos mis envíos →</a>
            </div>
        </div>
        <?php endif; ?>

    </main>

</div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
