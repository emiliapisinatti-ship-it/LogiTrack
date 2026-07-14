<?php
$page_subtitle = 'Panel Chofer';
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

/* Viaje activo */
.trip-card { background: linear-gradient(135deg, #1e1b4b, #312e81); border-radius: 12px; padding: 24px 28px; color: white; margin-bottom: 24px; }
.trip-card-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #a5b4fc; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
.trip-card-code { font-size: 13px; color: #c7d2fe; font-weight: 600; margin-bottom: 6px; }
.trip-card-origin { font-size: 18px; font-weight: 700; margin-bottom: 16px; }
.trip-card-details { display: flex; gap: 20px; flex-wrap: wrap; }
.trip-detail { font-size: 13px; color: #c7d2fe; }
.trip-detail strong { color: #fff; display: block; font-size: 16px; font-weight: 700; }
.trip-veh { margin-top: 16px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.15); display: flex; align-items: center; gap: 10px; }
.trip-veh i { color: #a5b4fc; font-size: 14px; }
.trip-veh-patente { font-family: monospace; font-size: 15px; font-weight: 700; color: #fff; }
.trip-veh-info { font-size: 12px; color: #c7d2fe; }
.no-trip { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 36px; text-align: center; margin-bottom: 24px; }
.no-trip i { font-size: 32px; color: #cbd5e1; display: block; margin-bottom: 10px; }
.no-trip p { color: #94a3b8; font-size: 14px; }

/* Secciones */
.dash-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
.dash-head { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px; }
.dash-head i { color: #6366f1; font-size: 13px; }
.dash-head-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #475569; }
.dash-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 11px 16px; border-bottom: 1px solid #f8fafc; }
.dash-row:last-child { border-bottom: none; }
.dr-main { font-size: 13px; font-weight: 600; color: #1e1b3a; }
.dr-sub  { font-size: 11px; color: #94a3b8; margin-top: 1px; }
.dr-right { font-size: 11px; color: #94a3b8; white-space: nowrap; margin-left: 8px; }
.dash-empty { padding: 20px 16px; text-align: center; color: #94a3b8; font-size: 13px; }
.badge-activo   { display:inline-block; padding:2px 9px; border-radius:20px; font-size:11px; font-weight:700; background:#e8f8ee; color:#3a6050; }
.badge-inactivo { display:inline-block; padding:2px 9px; border-radius:20px; font-size:11px; font-weight:700; background:#fde8e8; color:#b03030; }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
<div class="panel-wrap">

    <!-- SIDEBAR -->
    <aside class="p-sidebar">
        <div class="p-sidebar-label">Operaciones</div>
        <a href="/admin/router.php?pagina=mis_viajes"><i class="fa-solid fa-route"></i> Mis viajes</a>
        <a href="/admin/router.php?pagina=gestionar_envios"><i class="fa-solid fa-box-open"></i> Entregar paquete</a>
        <a href="/admin/router.php?pagina=incidentes"><i class="fa-solid fa-triangle-exclamation"></i> Incidentes</a>
        <a href="/admin/router.php?pagina=crear_incidente"><i class="fa-solid fa-plus"></i> Reportar incidente</a>
    </aside>

    <!-- MAIN -->
    <main class="p-main">

        <div class="p-greeting">
            <h1>Panel de chofer</h1>
            <p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong></p>
        </div>

        <?php
        $viaje_mostrar = $viaje_actual ?? $viaje_proximo ?? null;
        $es_activo     = !empty($viaje_actual);
        ?>

        <?php if ($viaje_mostrar): ?>
        <div class="trip-card" <?= !$es_activo ? 'style="background:linear-gradient(135deg,#374151,#1f2937);"' : '' ?>>
            <div class="trip-card-label">
                <?php if ($es_activo): ?>
                    <i class="fa-solid fa-circle" style="font-size:8px;color:#4ade80;"></i>
                    Viaje en curso
                <?php else: ?>
                    <i class="fa-solid fa-clock" style="font-size:12px;color:#fbbf24;"></i>
                    Próximo viaje
                <?php endif; ?>
            </div>
            <div class="trip-card-code"><?= htmlspecialchars($viaje_mostrar['cod_viaje']) ?></div>
            <div class="trip-card-origin">
                <i class="fa-solid fa-building" style="font-size:14px;color:#a5b4fc;margin-right:8px;"></i>
                <?= htmlspecialchars($viaje_mostrar['suc_origen'] ?? '—') ?>
                <?php if (!empty($viaje_mostrar['suc_destino'])): ?>
                <span style="color:#818cf8;font-size:14px;margin:0 6px;">→</span>
                <?= htmlspecialchars($viaje_mostrar['suc_destino']) ?>
                <?php endif; ?>
            </div>
            <div class="trip-card-details">
                <div class="trip-detail">
                    <strong><?= $viaje_paquetes ?></strong>
                    paquetes
                </div>
                <div class="trip-detail">
                    <strong><?= $viaje_mostrar['fecha_salida'] ? date('d/m/Y H:i', strtotime($viaje_mostrar['fecha_salida'])) : '—' ?></strong>
                    <?= $es_activo ? 'Salida' : 'Sale el' ?>
                </div>
                <div class="trip-detail">
                    <strong><?= $viaje_mostrar['fecha_llegada_est'] ? date('d/m/Y H:i', strtotime($viaje_mostrar['fecha_llegada_est'])) : '—' ?></strong>
                    Llegada estimada
                </div>
            </div>
            <?php if (!empty($viaje_mostrar['patente'])): ?>
            <div class="trip-veh">
                <i class="fa-solid fa-truck"></i>
                <div>
                    <div class="trip-veh-patente"><?= htmlspecialchars($viaje_mostrar['patente']) ?></div>
                    <div class="trip-veh-info">
                        <?= htmlspecialchars($viaje_mostrar['modelo'] ?? '') ?>
                        <?php if (!empty($viaje_mostrar['tipo_veh'])): ?>&nbsp;&bull;&nbsp;<?= htmlspecialchars($viaje_mostrar['tipo_veh']) ?><?php endif; ?>
                        <?php if (!empty($viaje_mostrar['capacidad_kg_max'])): ?>&nbsp;&bull;&nbsp;<?= number_format($viaje_mostrar['capacidad_kg_max'], 0) ?> kg máx.<?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="no-trip">
            <i class="fa-solid fa-truck-ramp-box"></i>
            <p>No tenés ningún viaje asignado en este momento.</p>
        </div>
        <?php endif; ?>

        <!-- Incidentes abiertos -->
        <?php if (!empty($chofer_incidentes)): ?>
        <div class="dash-section">
            <div class="dash-head">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span class="dash-head-title">Mis incidentes abiertos</span>
            </div>
            <?php foreach ($chofer_incidentes as $inc): ?>
            <div class="dash-row">
                <div>
                    <div class="dr-main">#<?= $inc['nro_incidente'] ?> &mdash; <?= htmlspecialchars($inc['tipo'] ?? 'Incidente') ?></div>
                    <div class="dr-sub"><?= htmlspecialchars(mb_strimwidth($inc['descripcion'] ?? '', 0, 60, '…')) ?></div>
                </div>
                <div class="dr-right"><?= $inc['fecha_hora'] ? date('d/m H:i', strtotime($inc['fecha_hora'])) : '—' ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Vehículos -->
        <?php if (!empty($chofer_vehiculos)): ?>
        <div class="dash-section">
            <div class="dash-head">
                <i class="fa-solid fa-truck"></i>
                <span class="dash-head-title">Mis vehículos</span>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr>
                        <th style="text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#94a3b8;padding:9px 16px;border-bottom:1.5px solid #e2e8f0;">Patente</th>
                        <th style="text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#94a3b8;padding:9px 16px;border-bottom:1.5px solid #e2e8f0;">Modelo</th>
                        <th style="text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#94a3b8;padding:9px 16px;border-bottom:1.5px solid #e2e8f0;">Tipo</th>
                        <th style="text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#94a3b8;padding:9px 16px;border-bottom:1.5px solid #e2e8f0;">Estado</th>
                        <th style="text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:#94a3b8;padding:9px 16px;border-bottom:1.5px solid #e2e8f0;">Último uso</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($chofer_vehiculos as $veh): ?>
                    <tr>
                        <td style="padding:10px 16px;border-bottom:1px solid #f8fafc;font-family:monospace;font-weight:700;color:#4f46e5;"><?= htmlspecialchars($veh['patente'] ?? '—') ?></td>
                        <td style="padding:10px 16px;border-bottom:1px solid #f8fafc;"><?= htmlspecialchars($veh['modelo'] ?? '—') ?></td>
                        <td style="padding:10px 16px;border-bottom:1px solid #f8fafc;color:#94a3b8;font-size:12px;"><?= htmlspecialchars($veh['tipo_veh'] ?? '—') ?></td>
                        <td style="padding:10px 16px;border-bottom:1px solid #f8fafc;">
                            <span class="<?= ($veh['estado'] ?? '') === 'Activo' ? 'badge-activo' : 'badge-inactivo' ?>"><?= htmlspecialchars($veh['estado'] ?? '—') ?></span>
                        </td>
                        <td style="padding:10px 16px;border-bottom:1px solid #f8fafc;color:#94a3b8;font-size:12px;"><?= !empty($veh['ultimo_uso']) ? date('d/m/Y', strtotime($veh['ultimo_uso'])) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </main>
</div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
