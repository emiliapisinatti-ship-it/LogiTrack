<?php
// vars: $error, $success, $paquetes, $vehiculos, $choferes, $nombre_sucursal
$page_subtitle = 'Despacho Masivo';
$nav_links = [['href' => '/admin/index.php', 'label' => '← Panel']];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height:100vh; }
    .lt-container { max-width: 1000px; }
    .layout { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }
    @media(max-width:750px){ .layout { grid-template-columns:1fr; } }
    table { width:100%; border-collapse:collapse; font-size:13px; }
    thead th { text-align:left; font-size:11px; font-weight:700; text-transform:uppercase;
               letter-spacing:0.8px; color:var(--text-soft); padding:9px 12px;
               border-bottom:1.5px solid var(--border); }
    tbody tr { border-bottom:1px solid var(--nude-dark); }
    tbody tr:hover { background:var(--nude); }
    tbody td { padding:10px 12px; vertical-align:middle; }
    .tracking-mono { font-family:monospace; font-weight:700; color:var(--rose-dark); font-size:12px; }
    .form-group { margin-bottom:14px; }
    .form-group label { display:block; font-size:11px; font-weight:700; text-transform:uppercase;
                        letter-spacing:0.5px; color:var(--text-soft); margin-bottom:5px; }
    .form-group select, .form-group input { width:100%; padding:9px 11px; border:1.5px solid var(--border);
        border-radius:9px; font-size:13px; background:var(--white); color:var(--text); box-sizing:border-box; }
    .form-group select:focus, .form-group input:focus { outline:none; border-color:var(--rose); }
    .btn-submit { width:100%; padding:11px; background:var(--rose-dark); color:#fff; border:none;
                  border-radius:9px; font-size:14px; font-weight:700; cursor:pointer; }
    .btn-submit:hover { opacity:0.88; }
    .alert-error { background:#fde8e8; border:1.5px solid #f5a0a0; color:#b03030;
                   border-radius:9px; padding:10px 14px; margin-bottom:14px; font-size:13px; }
    .alert-ok { background:#e8f8ee; border:1.5px solid #a0d0b0; color:#3a6050;
                border-radius:9px; padding:10px 14px; margin-bottom:14px; font-size:13px; }
    .chk-col { width:36px; text-align:center; }
    input[type="checkbox"] { width:16px; height:16px; cursor:pointer; }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
    <div style="font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:6px;">Despacho Masivo</div>
    <div style="color:var(--text-soft);font-size:13px;margin-bottom:20px;">
        Marcá los paquetes a despachar, asigná vehículo y chofer.
    </div>

    <?php if ($error):   ?><div class="alert-error">&#9888; <?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert-ok">&#10003; <?= $success ?></div><?php endif; ?>

    <!-- Filtro por sucursal destino (GET) -->
    <form method="get" action="/admin/router.php" style="margin-bottom:16px;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
        <input type="hidden" name="pagina" value="despacho">
        <div style="flex:1;min-width:200px;">
            <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-soft);margin-bottom:5px;">
                Sucursal destino *
            </label>
            <select name="suc_dest" style="width:100%;padding:9px 11px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;background:var(--white);">
                <option value="">— Todas —</option>
                <option value="domicilio" <?= $filtro_suc_dest === 'domicilio' ? 'selected' : '' ?>>📦 Entrega a domicilio</option>
                <?php foreach ($sucursales_destino as $s): ?>
                <option value="<?= $s['id_sucursal'] ?>" <?= $filtro_suc_dest == $s['id_sucursal'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" style="padding:9px 20px;background:#4f46e5;color:#fff;border:none;border-radius:9px;font-weight:600;cursor:pointer;white-space:nowrap;">
            Filtrar paquetes
        </button>
        <?php if ($filtro_suc_dest): ?>
        <a href="/admin/router.php?pagina=despacho" style="padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;color:var(--text-soft);text-decoration:none;">Limpiar</a>
        <?php endif; ?>
    </form>

    <form method="POST" action="/admin/router.php?pagina=despacho<?= $filtro_suc_dest ? '&suc_dest='.urlencode($filtro_suc_dest) : '' ?>">
    <input type="hidden" name="id_suc_destino" value="<?= $es_domicilio ? 0 : ($filtro_suc_dest ?: intval($_POST['id_suc_destino'] ?? 0)) ?>">
    <?php if ($es_domicilio): ?><input type="hidden" name="es_domicilio" value="1"><?php endif; ?>
    <div class="layout">

        <!-- Tabla de paquetes -->
        <div>
            <div style="font-weight:700;font-size:15px;margin-bottom:10px;">
                Paquetes en Depósito Origen
                <?php if ($nombre_sucursal ?? null): ?>
                <span style="font-weight:400;font-size:13px;color:var(--rose-dark);">
                    — <?= htmlspecialchars($nombre_sucursal) ?>
                </span>
                <?php endif; ?>
                <?php if ($filtro_suc_dest): ?>
                <span style="font-weight:400;font-size:13px;color:#4f46e5;">
                    → <?= htmlspecialchars(array_column($sucursales_destino, 'nombre', 'id_sucursal')[$filtro_suc_dest] ?? '') ?>
                </span>
                <?php endif; ?>
                <span style="font-weight:400;font-size:13px;color:var(--text-soft);">
                    (<?= count($paquetes) ?> disponibles)
                </span>
            </div>
            <div class="lt-card" style="padding:0;overflow:hidden;">
                <?php if (empty($paquetes)): ?>
                    <div style="text-align:center;padding:40px;color:var(--text-soft);">
                        <div style="font-size:36px;margin-bottom:8px;">&#128230;</div>
                        <em>No hay paquetes en Depósito Origen.</em>
                    </div>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th class="chk-col">&#10003;</th>
                            <th>Tracking</th>
                            <th>Remitente</th>
                            <th>Destino</th>
                            <th>Peso (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($paquetes as $p): ?>
                        <tr>
                            <td class="chk-col">
                                <input type="checkbox" name="trackings[]"
                                       value="<?= htmlspecialchars($p['nro_tracking']) ?>"
                                       class="chk-p"
                                       <?= in_array($p['nro_tracking'], $_POST['trackings'] ?? []) ? 'checked' : '' ?>>
                            </td>
                            <td><span class="tracking-mono"><?= htmlspecialchars($p['nro_tracking']) ?></span></td>
                            <td><?= htmlspecialchars($p['ape_rem'] . ', ' . $p['nom_rem']) ?></td>
                            <td style="font-size:12px;">
                                <?= $p['suc_destino']
                                    ? htmlspecialchars($p['suc_destino'])
                                    : htmlspecialchars($p['direccion_entrega'] ?? '-') ?>
                            </td>
                            <td style="font-weight:600;"><?= number_format($p['peso_kg'], 1) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Panel derecho: vehículo, chofer, fechas -->
        <div>
            <div style="font-weight:700;font-size:15px;margin-bottom:10px;">Configurar viaje</div>
            <div class="lt-card">

                <?php if (!$filtro_suc_dest): ?>
                <div style="background:#fef9ec;border:1.5px solid #fbbf24;border-radius:9px;padding:10px 12px;margin-bottom:14px;font-size:13px;color:#92400e;">
                    ⚠️ Seleccioná primero el tipo de destino para filtrar los paquetes.
                </div>
                <?php elseif ($es_domicilio): ?>
                <div class="form-group">
                    <label>Tipo de destino</label>
                    <div style="padding:9px 11px;border:1.5px solid #a5b4fc;border-radius:9px;font-size:13px;background:#eff6ff;color:#3730a3;font-weight:600;">
                        Entrega a domicilio (el chofer va directo a la dirección del destinatario)
                    </div>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label>Sucursal destino</label>
                    <div style="padding:9px 11px;border:1.5px solid #a5b4fc;border-radius:9px;font-size:13px;background:#eff6ff;color:#3730a3;font-weight:600;">
                        <?= htmlspecialchars(array_column($sucursales_destino, 'nombre', 'id_sucursal')[intval($filtro_suc_dest)] ?? '—') ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Vehículo *</label>
                    <select name="patente" required>
                        <option value="">— Seleccioná vehículo —</option>
                        <?php foreach ($vehiculos as $v): ?>
                        <option value="<?= htmlspecialchars($v['patente']) ?>"
                                <?= ($_POST['patente'] ?? '') === $v['patente'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['patente']) ?>
                            — <?= htmlspecialchars($v['tipo']) ?>
                            (<?= number_format($v['capacidad_kg_max'], 0) ?> kg máx.)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Chofer *</label>
                    <select name="legajo_chofer" required>
                        <option value="">— Seleccioná chofer —</option>
                        <?php foreach ($choferes as $c): ?>
                        <option value="<?= htmlspecialchars($c['legajo_chofer']) ?>"
                                <?= ($_POST['legajo_chofer'] ?? '') === $c['legajo_chofer'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre']) ?>
                            (Lic. <?= htmlspecialchars($c['id_licencia']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Fecha y hora de salida *</label>
                    <input type="datetime-local" name="fecha_salida" required
                           value="<?= htmlspecialchars($_POST['fecha_salida'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Llegada estimada *</label>
                    <input type="datetime-local" name="fecha_llegada_est" required
                           value="<?= htmlspecialchars($_POST['fecha_llegada_est'] ?? '') ?>">
                </div>

                <div style="background:var(--nude);border-radius:9px;padding:10px 12px;
                            margin-bottom:14px;font-size:13px;color:var(--text-soft);">
                    &#8505; El sistema verifica automáticamente que el peso total no supere
                    la capacidad del vehículo seleccionado.
                </div>

                <button type="submit" class="btn-submit">Despachar viaje</button>
            </div>
        </div>
    </div>
    </form>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
