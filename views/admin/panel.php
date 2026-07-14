<?php
$page_subtitle = 'Administracion';
$nav_links = [];
$extra_css = '
body { background: #f1f5f9; }

/* ── Layout ── */
.lt-container { max-width: 100%; margin: 0; padding: 0; }
.panel-wrap   { display: flex; min-height: calc(100vh - 56px); }

/* ── Sidebar ── */
.p-sidebar {
    width: 220px;
    flex-shrink: 0;
    background: #1e1b4b;
    padding: 28px 0 20px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    position: sticky;
    top: 56px;
    height: calc(100vh - 56px);
    overflow-y: auto;
}
.p-sidebar-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #6366f1;
    padding: 0 20px 8px;
    margin-top: 4px;
}
.p-sidebar a {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 20px;
    color: #c7d2fe;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-left: 3px solid transparent;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.p-sidebar a:hover {
    background: rgba(99,102,241,0.18);
    color: #fff;
    border-left-color: #818cf8;
}
.p-sidebar a i {
    width: 17px;
    text-align: center;
    font-size: 14px;
    opacity: 0.85;
}

/* ── Contenido ── */
.p-main { flex: 1; padding: 32px 36px; min-width: 0; }

.p-greeting { margin-bottom: 28px; }
.p-greeting h1 {
    font-size: 24px;
    font-weight: 700;
    color: #1e1b3a;
    margin-bottom: 4px;
    font-family: "DM Serif Display", serif;
}
.p-greeting p { font-size: 14px; color: #64748b; }

/* ── Métricas ── */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
@media(max-width:900px){ .metrics-grid { grid-template-columns: repeat(2,1fr); } }
.metric-card {
    background: #fff;
    border-radius: 14px;
    padding: 22px 24px;
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
    transition: box-shadow 0.2s, transform 0.15s;
}
.metric-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.10); transform: translateY(-2px); }
.metric-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 20px;
}
.mi-red    { background: #fee2e2; color: #dc2626; }
.mi-amber  { background: #fef3c7; color: #d97706; }
.mi-blue   { background: #dbeafe; color: #2563eb; }
.mi-green  { background: #dcfce7; color: #16a34a; }
.mi-indigo { background: #ede9fe; color: #7c3aed; }
.metric-body { min-width: 0; }
.metric-val {
    font-size: 34px;
    font-weight: 700;
    color: #1e1b3a;
    line-height: 1;
    margin-bottom: 4px;
}
.metric-lbl {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Sección de actividad ── */
.activity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media(max-width:700px){ .activity-grid { grid-template-columns: 1fr; } }
.act-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.act-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
}
.act-head-title {
    display: flex;
    align-items: center;
    gap: 9px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #475569;
}
.act-head-title i { color: #6366f1; font-size: 13px; }
.act-head a {
    font-size: 12px;
    color: #6366f1;
    text-decoration: none;
    font-weight: 600;
}
.act-head a:hover { text-decoration: underline; }
.act-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 12px 20px;
    border-bottom: 1px solid #f8fafc;
    text-decoration: none;
    color: inherit;
    transition: background 0.12s;
}
.act-row:last-child { border-bottom: none; }
.act-row:hover { background: #f8fafc; }
.act-main { font-size: 14px; font-weight: 600; color: #1e1b3a; margin-bottom: 2px; }
.act-sub  { font-size: 12px; color: #94a3b8; }
.act-time {
    font-size: 11px;
    color: #94a3b8;
    white-space: nowrap;
    margin-left: 12px;
    padding-top: 2px;
    flex-shrink: 0;
}
.act-empty {
    padding: 36px 20px;
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
}
.act-empty i { font-size: 28px; display: block; margin-bottom: 10px; opacity: 0.4; }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
<div class="panel-wrap">

    <!-- ── SIDEBAR ── -->
    <aside class="p-sidebar">
        <div class="p-sidebar-label">Gestión</div>
        <a href="/admin/router.php?pagina=envios"><i class="fa-solid fa-box"></i> Envíos</a>
        <a href="/admin/router.php?pagina=viajes"><i class="fa-solid fa-route"></i> Viajes</a>
        <a href="/admin/router.php?pagina=incidentes"><i class="fa-solid fa-triangle-exclamation"></i> Incidentes</a>
        <a href="/admin/router.php?pagina=usuarios"><i class="fa-solid fa-users"></i> Usuarios</a>
        <a href="/admin/router.php?pagina=sucursales"><i class="fa-solid fa-building"></i> Sucursales</a>
        <a href="/admin/router.php?pagina=vehiculos"><i class="fa-solid fa-truck"></i> Vehículos</a>

        <div class="p-sidebar-label" style="margin-top:12px;">Reportes</div>
        <a href="/admin/router.php?pagina=reportes"><i class="fa-solid fa-chart-bar"></i> Estadísticas</a>
        <a href="/admin/router.php?pagina=auditoria"><i class="fa-solid fa-shield-halved"></i> Auditoría</a>
    </aside>

    <!-- ── MAIN ── -->
    <main class="p-main">

        <!-- Saludo -->
        <div class="p-greeting">
            <h1>Panel de administración</h1>
            <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> &mdash; <?= date('d/m/Y') ?></p>
        </div>

        <!-- Métricas -->
        <div class="metrics-grid">
            <a href="/admin/router.php?pagina=incidentes&estado=abierto" class="metric-card">
                <div class="metric-icon mi-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="metric-body">
                    <div class="metric-val"><?= $kpi_incidentes ?></div>
                    <div class="metric-lbl">Incidentes abiertos</div>
                </div>
            </a>
            <a href="/admin/router.php?pagina=envios&estado=<?= $id_est_ini ?>" class="metric-card">
                <div class="metric-icon mi-amber"><i class="fa-solid fa-box"></i></div>
                <div class="metric-body">
                    <div class="metric-val"><?= $kpi_pendientes ?></div>
                    <div class="metric-lbl">En depósito</div>
                </div>
            </a>
            <a href="/admin/router.php?pagina=viajes&estado=en_curso" class="metric-card">
                <div class="metric-icon mi-blue"><i class="fa-solid fa-route"></i></div>
                <div class="metric-body">
                    <div class="metric-val"><?= $kpi_viajes ?></div>
                    <div class="metric-lbl">Viajes en curso</div>
                </div>
            </a>
            <a href="/admin/router.php?pagina=envios" class="metric-card">
                <div class="metric-icon mi-indigo"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div class="metric-body">
                    <div class="metric-val"><?= $kpi_envios ?></div>
                    <div class="metric-lbl">Envíos totales</div>
                </div>
            </a>
        </div>

        <!-- Actividad reciente -->
        <div class="activity-grid">

            <!-- Viajes en curso -->
            <div class="act-card">
                <div class="act-head">
                    <div class="act-head-title">
                        <i class="fa-solid fa-route"></i> Viajes en curso
                    </div>
                    <a href="/admin/router.php?pagina=viajes&estado=en_curso">Ver todos →</a>
                </div>
                <?php if (empty($viajes_activos)): ?>
                    <div class="act-empty">
                        <i class="fa-solid fa-truck"></i>
                        Sin viajes activos
                    </div>
                <?php else: foreach ($viajes_activos as $v): ?>
                    <a href="/admin/router.php?pagina=ver_viaje&cod=<?= urlencode($v['cod_viaje']) ?>" class="act-row">
                        <div>
                            <div class="act-main"><?= htmlspecialchars($v['cod_viaje']) ?> &mdash; <?= htmlspecialchars($v['suc_origen'] ?? '—') ?></div>
                            <div class="act-sub">Chofer: <?= htmlspecialchars(trim(($v['nom_chofer'] ?? '') . ' ' . ($v['ape_chofer'] ?? ''))) ?: '—' ?></div>
                        </div>
                        <div class="act-time"><?= $v['fecha_salida'] ? date('d/m H:i', strtotime($v['fecha_salida'])) : '—' ?></div>
                    </a>
                <?php endforeach; endif; ?>
            </div>

            <!-- Incidentes abiertos -->
            <div class="act-card">
                <div class="act-head">
                    <div class="act-head-title">
                        <i class="fa-solid fa-triangle-exclamation"></i> Incidentes abiertos
                    </div>
                    <a href="/admin/router.php?pagina=incidentes&estado=abierto">Ver todos →</a>
                </div>
                <?php if (empty($incidentes_recientes)): ?>
                    <div class="act-empty">
                        <i class="fa-solid fa-circle-check"></i>
                        Sin incidentes abiertos
                    </div>
                <?php else: foreach ($incidentes_recientes as $inc): ?>
                    <a href="/admin/router.php?pagina=incidentes&q=<?= urlencode($inc['cod_viaje'] ?? '') ?>" class="act-row">
                        <div>
                            <div class="act-main">#<?= $inc['nro_incidente'] ?> — <?= htmlspecialchars($inc['tipo'] ?? 'Incidente') ?></div>
                            <div class="act-sub"><?= htmlspecialchars(mb_strimwidth($inc['descripcion'] ?? '', 0, 55, '…')) ?></div>
                        </div>
                        <div class="act-time"><?= $inc['fecha_hora'] ? date('d/m H:i', strtotime($inc['fecha_hora'])) : '—' ?></div>
                    </a>
                <?php endforeach; endif; ?>
            </div>

        </div><!-- /activity-grid -->
    </main>

</div><!-- /panel-wrap -->
</div><!-- /lt-container -->
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
