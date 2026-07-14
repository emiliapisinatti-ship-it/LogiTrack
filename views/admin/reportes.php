<?php
// vars: $total_envios, $entregados, $total_viajes, $total_clientes, $tasa_entrega,
//       $mes_labels, $mes_data, $est_labels, $est_data,
//       $por_sucursal, $por_tipo_inc, $top_choferes
$page_subtitle = 'Reportes';
$nav_links = [['href' => '/admin/index.php', 'label' => '← Panel']];

$mes_labels_arr  = json_decode($mes_labels  ?? '[]', true) ?: [];
$mes_data_arr    = json_decode($mes_data    ?? '[]', true) ?: [];
$est_labels_arr  = json_decode($est_labels  ?? '[]', true) ?: [];
$est_data_arr    = json_decode($est_data    ?? '[]', true) ?: [];

$mes_max   = max(array_merge($mes_data_arr, [1]));
$est_total = array_sum($est_data_arr) ?: 1;
$suc_max   = max(array_merge(array_column($por_sucursal ?? [], 'total'), [1]));
$inc_total = array_sum(array_column($por_tipo_inc ?? [], 'total')) ?: 1;

$paleta = ['#4f46e5','#818cf8','#6366f1','#a5b4fc','#3730a3','#7c3aed','#8b5cf6','#c4b5fd'];

$extra_css = '
body { background: linear-gradient(145deg,#f0f0ff 0%,#eef2ff 100%); min-height: 100vh; }
.lt-container { max-width: 1060px; }
.rep-title { font-family:"DM Serif Display",serif; font-size:22px; margin-bottom:20px; color:var(--text); }

/* KPIs principales */
.kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:12px; }
@media(max-width:600px){ .kpi-grid { grid-template-columns:repeat(2,1fr); } }
.kpi-card { background:var(--white); border:1px solid var(--border); border-left:3px solid var(--rose-dark); border-radius:8px; padding:16px 18px; text-decoration:none; color:inherit; display:block; transition:box-shadow 0.15s, transform 0.12s; }
.kpi-card:hover { box-shadow:0 4px 14px rgba(0,0,0,0.10); transform:translateY(-2px); border-color:var(--rose-dark); }
.kpi-val  { font-size:28px; font-weight:700; color:var(--text); line-height:1; margin-bottom:3px; }
.kpi-lbl  { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.6px; color:var(--text-soft); }

/* Tasa de entrega */
.tasa-card { background:var(--white); border:1px solid var(--border); border-radius:8px; padding:18px 22px; margin-bottom:20px; display:flex; align-items:center; gap:24px; }
.tasa-pct  { font-size:42px; font-weight:700; color:var(--rose-dark); line-height:1; flex-shrink:0; }
.tasa-info { flex:1; }
.tasa-lbl  { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-soft); margin-bottom:8px; }
.tasa-bar-track { background:var(--nude-dark); border-radius:6px; height:10px; overflow:hidden; margin-bottom:6px; }
.tasa-bar-fill  { height:100%; border-radius:6px; background:linear-gradient(90deg,#4f46e5,#818cf8); }
.tasa-sub  { font-size:12px; color:var(--text-soft); }

/* Cards de gráficos */
.charts-grid  { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; }
.charts-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:16px; }
@media(max-width:700px){ .charts-grid, .charts-grid-3 { grid-template-columns:1fr; } }
.chart-card { background:var(--white); border:1px solid var(--border); border-radius:10px; padding:20px; }
.chart-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-soft); margin-bottom:16px; }

/* Barras horizontales */
.bar-chart { display:flex; flex-direction:column; gap:7px; }
.bar-row   { display:flex; align-items:center; gap:10px; }
.bar-lbl   { width:64px; font-size:11px; color:var(--text-soft); text-align:right; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex-shrink:0; }
.bar-lbl-wide { width:160px; }
.bar-track { flex:1; background:var(--nude-dark); border-radius:4px; height:20px; overflow:hidden; position:relative; }
.bar-fill  { height:100%; border-radius:4px; background:var(--rose-dark); display:flex; align-items:center; justify-content:flex-end; min-width:4px; }
.bar-val   { font-size:10px; font-weight:700; color:#fff; padding-right:5px; white-space:nowrap; }
.bar-val-out { font-size:11px; color:var(--text-soft); padding-left:5px; }

/* Distribución por estado */
.dist-chart { display:flex; flex-direction:column; gap:9px; }
.dist-row   { display:flex; align-items:center; gap:9px; }
.dist-dot   { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.dist-lbl   { font-size:12px; color:var(--text); min-width:110px; }
.dist-track { flex:1; background:var(--nude-dark); border-radius:3px; height:14px; overflow:hidden; }
.dist-fill  { height:100%; border-radius:3px; }
.dist-cnt   { font-size:11px; font-weight:700; color:var(--text-soft); width:28px; text-align:right; }

/* Filas clickeables */
.row-link { display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid var(--nude-dark); text-decoration:none; color:inherit; border-radius:6px; transition:background 0.12s; cursor:pointer; }
.row-link:last-child { border-bottom:none; }
.row-link:hover { background:var(--nude); padding-left:6px; }
.dist-row-link { display:flex; align-items:center; gap:9px; padding:5px 6px; border-radius:6px; text-decoration:none; color:inherit; transition:background 0.12s; }
.dist-row-link:hover { background:var(--nude); }

/* Top choferes */
.chofer-rank { width:22px; height:22px; border-radius:50%; background:var(--nude); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:var(--rose-dark); flex-shrink:0; }
.chofer-rank.gold   { background:#fef3c7; color:#92400e; }
.chofer-rank.silver { background:#f1f5f9; color:#475569; }
.chofer-rank.bronze { background:#fdf4ea; color:#92400e; }
.chofer-name { flex:1; font-size:13px; font-weight:500; color:var(--text); }
.chofer-stat { font-size:12px; color:var(--text-soft); }
.chofer-stat strong { color:var(--rose-dark); }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
    <div class="rep-title">Reportes</div>

    <!-- KPIs principales -->
    <div class="kpi-grid">
        <a href="/admin/router.php?pagina=envios" class="kpi-card"><div class="kpi-val"><?= $total_envios ?></div><div class="kpi-lbl">Envíos totales</div></a>
        <a href="/admin/router.php?pagina=envios" class="kpi-card"><div class="kpi-val"><?= $entregados ?></div><div class="kpi-lbl">Entregados</div></a>
        <a href="/admin/router.php?pagina=viajes" class="kpi-card"><div class="kpi-val"><?= $total_viajes ?></div><div class="kpi-lbl">Viajes</div></a>
        <a href="/admin/router.php?pagina=usuarios&rol=4" class="kpi-card"><div class="kpi-val"><?= $total_clientes ?></div><div class="kpi-lbl">Clientes</div></a>
    </div>

    <!-- Tasa de entrega -->
    <div class="tasa-card">
        <div class="tasa-pct"><?= $tasa_entrega ?>%</div>
        <div class="tasa-info">
            <div class="tasa-lbl">Tasa de entrega exitosa</div>
            <div class="tasa-bar-track">
                <div class="tasa-bar-fill" style="width:<?= $tasa_entrega ?>%"></div>
            </div>
            <div class="tasa-sub"><?= $entregados ?> de <?= $total_envios ?> envíos llegaron a destino</div>
        </div>
    </div>

    <!-- Fila 1: envíos por mes + por estado -->
    <div class="charts-grid">

        <div class="chart-card">
            <div class="chart-title">Envíos por mes</div>
            <?php if (empty($mes_data_arr)): ?>
                <div style="color:var(--text-soft);font-size:13px;text-align:center;padding:20px;">Sin datos</div>
            <?php else: ?>
            <div class="bar-chart">
                <?php foreach ($mes_labels_arr as $i => $label):
                    $val = $mes_data_arr[$i] ?? 0;
                    $pct = $mes_max > 0 ? round(($val / $mes_max) * 100) : 0;
                ?>
                <div class="bar-row">
                    <div class="bar-lbl" title="<?= htmlspecialchars((string)$label) ?>"><?= htmlspecialchars((string)$label) ?></div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:<?= max($pct, $val > 0 ? 5 : 0) ?>%">
                            <?php if ($pct >= 18): ?><span class="bar-val"><?= $val ?></span><?php endif; ?>
                        </div>
                    </div>
                    <?php if ($pct < 18 && $val > 0): ?><span class="bar-val-out"><?= $val ?></span><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="chart-card">
            <div class="chart-title">Envíos por estado actual</div>
            <?php if (empty($est_data_arr)): ?>
                <div style="color:var(--text-soft);font-size:13px;text-align:center;padding:20px;">Sin datos</div>
            <?php else: ?>
            <div class="dist-chart">
                <?php foreach ($est_labels_arr as $i => $label):
                    $val    = $est_data_arr[$i] ?? 0;
                    $pct    = $est_total > 0 ? round(($val / $est_total) * 100) : 0;
                    $color  = $paleta[$i % count($paleta)];
                    $id_est = $por_estado[$i]['id_estado'] ?? 0;
                    $href   = $id_est ? "/admin/router.php?pagina=envios&estado={$id_est}" : "/admin/router.php?pagina=envios";
                ?>
                <a href="<?= $href ?>" class="dist-row-link">
                    <div class="dist-dot" style="background:<?= $color ?>"></div>
                    <div class="dist-lbl"><?= htmlspecialchars((string)$label) ?></div>
                    <div class="dist-track"><div class="dist-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
                    <div class="dist-cnt"><?= $val ?></div>
                </a>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:12px;font-size:11px;color:var(--text-soft);text-align:right;">Total: <?= array_sum($est_data_arr) ?> envíos</div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Fila 2: por sucursal + incidentes por tipo + top choferes -->
    <div class="charts-grid-3">

        <div class="chart-card">
            <div class="chart-title">Envíos por sucursal</div>
            <?php if (empty($por_sucursal)): ?>
                <div style="color:var(--text-soft);font-size:13px;text-align:center;padding:20px;">Sin datos</div>
            <?php else: ?>
            <div class="bar-chart">
                <?php foreach ($por_sucursal as $row):
                    $pct = $suc_max > 0 ? round(($row['total'] / $suc_max) * 100) : 0;
                ?>
                <a href="/admin/router.php?pagina=envios&sucursal=<?= $row['id_sucursal'] ?>" class="bar-row" style="text-decoration:none;border-radius:4px;padding:2px 4px;transition:background 0.12s;" onmouseover="this.style.background='var(--nude)'" onmouseout="this.style.background=''">
                    <div class="bar-lbl bar-lbl-wide" title="<?= htmlspecialchars($row['sucursal']) ?>"><?= htmlspecialchars($row['sucursal']) ?></div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:<?= max($pct, 5) ?>%">
                            <?php if ($pct >= 18): ?><span class="bar-val"><?= $row['total'] ?></span><?php endif; ?>
                        </div>
                    </div>
                    <?php if ($pct < 18): ?><span class="bar-val-out"><?= $row['total'] ?></span><?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="chart-card">
            <div class="chart-title">Incidentes por tipo</div>
            <?php if (empty($por_tipo_inc)): ?>
                <div style="color:var(--text-soft);font-size:13px;text-align:center;padding:20px;">Sin incidentes registrados</div>
            <?php else: ?>
            <div class="dist-chart">
                <?php foreach ($por_tipo_inc as $i => $row):
                    $pct   = $inc_total > 0 ? round(($row['total'] / $inc_total) * 100) : 0;
                    $color = $paleta[$i % count($paleta)];
                ?>
                <a href="/admin/router.php?pagina=incidentes" class="dist-row-link">
                    <div class="dist-dot" style="background:<?= $color ?>"></div>
                    <div class="dist-lbl"><?= htmlspecialchars($row['tipo']) ?></div>
                    <div class="dist-track"><div class="dist-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
                    <div class="dist-cnt"><?= $row['total'] ?></div>
                </a>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:12px;font-size:11px;color:var(--text-soft);text-align:right;">Total: <?= array_sum(array_column($por_tipo_inc,'total')) ?> incidentes</div>
            <?php endif; ?>
        </div>

        <div class="chart-card">
            <div class="chart-title">Top choferes</div>
            <?php if (empty($top_choferes)): ?>
                <div style="color:var(--text-soft);font-size:13px;text-align:center;padding:20px;">Sin datos</div>
            <?php else: ?>
            <?php foreach ($top_choferes as $i => $ch):
                $rankClass = match($i) { 0 => 'gold', 1 => 'silver', 2 => 'bronze', default => '' };
                $q = urlencode($ch['legajo_chofer']);
            ?>
            <a href="/admin/router.php?pagina=viajes&q=<?= $q ?>" class="row-link">
                <div class="chofer-rank <?= $rankClass ?>"><?= $i + 1 ?></div>
                <div class="chofer-name"><?= htmlspecialchars($ch['nombre'] . ' ' . $ch['apellido']) ?></div>
                <div class="chofer-stat">
                    <strong><?= $ch['total_viajes'] ?></strong> viajes
                    &nbsp;&bull;&nbsp; <?= $ch['completados'] ?> completados
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
