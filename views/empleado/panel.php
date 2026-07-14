<?php
$page_subtitle = 'Panel Empleado';
$nav_links = [];
$extra_css = '
body { background: #f1f5f9; }
.lt-container { max-width: 100%; margin: 0; padding: 0; }
.panel-wrap { display: flex; min-height: calc(100vh - 56px); }

/* Sidebar */
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

/* Main */
.p-main { flex: 1; padding: 32px 36px; min-width: 0; }
.p-greeting { margin-bottom: 28px; }
.p-greeting h1 { font-size: 24px; font-weight: 700; color: #1e1b3a; margin-bottom: 4px; font-family: "DM Serif Display", serif; }
.p-greeting p { font-size: 14px; color: #64748b; }

/* Cards alertas */
.alert-bar { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 28px; }
.alert-card { display: flex; align-items: center; gap: 14px; padding: 18px 22px; border-radius: 12px; border: 1.5px solid; flex: 1; min-width: 150px; text-decoration: none; transition: box-shadow 0.2s, transform 0.15s; }
.alert-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.10); transform: translateY(-2px); }
.alert-card i { font-size: 22px; flex-shrink: 0; }
.ac-val { font-size: 26px; font-weight: 700; line-height: 1; }
.ac-lbl { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 3px; }
.alert-red   { background: #fee2e2; border-color: #fca5a5; color: #b91c1c; }
.alert-amber { background: #fef3c7; border-color: #fcd34d; color: #92400e; }
.alert-blue  { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }
.alert-green { background: #f0faf4; border-color: #86efac; color: #15803d; }

/* Grilla actividad */
.dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 0; }
@media(max-width:650px){ .dash-grid { grid-template-columns: 1fr; } }
.dash-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.dash-head { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px; }
.dash-head i { color: #6366f1; font-size: 13px; }
.dash-head-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #475569; }
.dash-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 11px 16px; border-bottom: 1px solid #f8fafc; }
.dash-row:last-child { border-bottom: none; }
.dr-main { font-size: 13px; font-weight: 600; color: #1e1b3a; }
.dr-sub  { font-size: 11px; color: #94a3b8; margin-top: 1px; }
.dr-right { font-size: 11px; color: #94a3b8; white-space: nowrap; margin-left: 8px; padding-top: 1px; }
.dash-empty { padding: 28px 16px; text-align: center; color: #94a3b8; font-size: 13px; }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
<div class="panel-wrap">

    <!-- SIDEBAR -->
    <aside class="p-sidebar">
        <div class="p-sidebar-label">Operaciones</div>
        <a href="/admin/router.php?pagina=despacho"><i class="fa-solid fa-truck-fast"></i> Despacho</a>
        <a href="/admin/router.php?pagina=viajes"><i class="fa-solid fa-route"></i> Viajes</a>
        <a href="/admin/router.php?pagina=envios"><i class="fa-solid fa-box"></i> Envíos</a>
        <a href="/admin/router.php?pagina=gestionar_envios"><i class="fa-solid fa-pen-to-square"></i> Gestionar envío</a>
        <a href="/admin/router.php?pagina=incidentes"><i class="fa-solid fa-triangle-exclamation"></i> Incidentes</a>
    </aside>

    <!-- MAIN -->
    <main class="p-main">

        <div class="p-greeting">
            <h1>Panel de empleado</h1>
            <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong>
            <?php if ($nombre_sucursal_emp): ?> &mdash; Sucursal <strong><?= htmlspecialchars($nombre_sucursal_emp) ?></strong><?php endif; ?></p>
        </div>

        <!-- Cards -->
        <div class="alert-bar">
            <a href="/admin/router.php?pagina=despacho" class="alert-card <?= $emp_pendientes > 0 ? 'alert-amber' : 'alert-green' ?>">
                <i class="fa-solid fa-box"></i>
                <div>
                    <div class="ac-val"><?= $emp_pendientes ?></div>
                    <div class="ac-lbl">Paquetes en depósito</div>
                </div>
            </a>
            <a href="/admin/router.php?pagina=incidentes&estado=abierto" class="alert-card <?= $emp_incidentes > 0 ? 'alert-red' : 'alert-green' ?>">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    <div class="ac-val"><?= $emp_incidentes ?></div>
                    <div class="ac-lbl">Incidentes abiertos</div>
                </div>
            </a>
            <a href="/admin/router.php?pagina=viajes" class="alert-card alert-blue">
                <i class="fa-solid fa-route"></i>
                <div>
                    <div class="ac-val"><?= $emp_viajes_activos ?></div>
                    <div class="ac-lbl">Viajes activos</div>
                </div>
            </a>
        </div>

        <!-- Actividad -->
        <div class="dash-grid">
            <div class="dash-section">
                <div class="dash-head">
                    <i class="fa-solid fa-box"></i>
                    <span class="dash-head-title">Paquetes pendientes de despacho</span>
                </div>
                <?php if (empty($emp_envios_lista)): ?>
                    <div class="dash-empty"><i class="fa-solid fa-check" style="color:#15803d;margin-right:6px;"></i>No hay paquetes pendientes.</div>
                <?php else: foreach ($emp_envios_lista as $env): ?>
                    <div class="dash-row">
                        <div>
                            <div class="dr-main"><?= htmlspecialchars($env['nro_tracking']) ?></div>
                            <div class="dr-sub">Para: <?= htmlspecialchars(trim($env['destinatario'] ?? '—')) ?></div>
                        </div>
                        <div class="dr-right"><?= htmlspecialchars($env['suc_destino'] ?? '—') ?></div>
                    </div>
                <?php endforeach; endif; ?>
                <?php if ($emp_pendientes > count($emp_envios_lista)): ?>
                    <div style="padding:8px 16px;font-size:11px;color:#94a3b8;border-top:1px solid #f1f5f9;">
                        +<?= $emp_pendientes - count($emp_envios_lista) ?> más —
                        <a href="/admin/router.php?pagina=despacho" style="color:#6366f1;text-decoration:none;font-weight:600;">Ver todos</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dash-section">
                <div class="dash-head">
                    <i class="fa-solid fa-route"></i>
                    <span class="dash-head-title">Viajes activos de tu sucursal</span>
                </div>
                <?php if (empty($emp_viajes_lista)): ?>
                    <div class="dash-empty">No hay viajes activos en tu sucursal.</div>
                <?php else: foreach ($emp_viajes_lista as $v): ?>
                    <div class="dash-row">
                        <div>
                            <div class="dr-main"><?= htmlspecialchars($v['cod_viaje']) ?></div>
                            <div class="dr-sub">
                                Chofer: <?= htmlspecialchars(trim(($v['nom_chofer'] ?? '') . ' ' . ($v['ape_chofer'] ?? ''))) ?: '—' ?>
                                &nbsp;&bull;&nbsp; <?= $v['total_envios'] ?> paquetes
                            </div>
                        </div>
                        <div class="dr-right"><?= $v['fecha_salida'] ? date('d/m H:i', strtotime($v['fecha_salida'])) : '—' ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

    </main>
</div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
