<?php
// vars: $error, $choferes, $vehiculos, $sucursales, $envios_sin_viaje, $id_suc_origen
$page_subtitle = 'Nuevo Viaje';
$nav_links = [['href' => '/admin/router.php?pagina=viajes', 'label' => '← Viajes']];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 700px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase;
                        letter-spacing: 0.5px; color: var(--text-soft); margin-bottom: 6px; }
    .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 1.5px solid var(--border);
        border-radius: 10px; font-size: 14px; background: var(--white); color: var(--text);
        box-sizing: border-box; transition: border-color 0.2s; }
    .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--rose); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .envios-list { max-height: 220px; overflow-y: auto; border: 1.5px solid var(--border);
                   border-radius: 10px; padding: 8px; background: var(--white); }
    .envio-item { display: flex; align-items: center; gap: 8px; padding: 6px 4px;
                  border-bottom: 1px solid var(--nude-dark); font-size: 13px; }
    .envio-item:last-child { border-bottom: none; }
    .btn-submit { width: 100%; padding: 12px; background: var(--rose-dark); color: #fff;
                  border: none; border-radius: 10px; font-size: 15px; font-weight: 700;
                  cursor: pointer; margin-top: 8px; transition: opacity 0.2s; }
    .btn-submit:hover { opacity: 0.88; }
    .alert-error { background: #fde8e8; border: 1.5px solid #f5a0a0; color: #b03030;
                   border-radius: 10px; padding: 10px 14px; margin-bottom: 16px; font-size: 14px; }
    .info-readonly { background: var(--nude); border-radius: 8px; padding: 8px 12px;
                     font-size: 13px; color: var(--text-soft); margin-bottom: 16px; }
    .info-readonly strong { color: var(--text); }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);margin-bottom:20px;">Nuevo viaje</div>

    <?php if ($error): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (!$id_suc_origen): ?>
    <div class="lt-card">
        <form method="GET">
            <input type="hidden" name="pagina" value="crear_viaje">
            <div class="form-group">
                <label>Sucursal origen *</label>
                <select name="suc_origen" required>
                    <option value="">— Seleccioná una sucursal —</option>
                    <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s['id_sucursal'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="font-size:11px;color:var(--text-soft);margin:-8px 0 14px;">
                El chofer, el vehículo y los envíos disponibles dependen de la sucursal elegida.
            </div>
            <button type="submit" class="btn-submit">Continuar</button>
        </form>
    </div>
    <?php else: ?>
    <?php
        $nombre_origen = '';
        foreach ($sucursales as $s) {
            if ($s['id_sucursal'] == $id_suc_origen) { $nombre_origen = $s['nombre']; break; }
        }
    ?>
    <div class="lt-card">
        <form method="POST">
            <input type="hidden" name="id_suc_origen" value="<?= $id_suc_origen ?>">
            <div class="form-group">
                <label>Sucursal origen</label>
                <div class="info-readonly" style="margin:0;">
                    <strong><?= htmlspecialchars($nombre_origen) ?></strong>
                    — <a href="/admin/router.php?pagina=crear_viaje">cambiar sucursal</a>
                </div>
            </div>

            <?php if (empty($choferes) || empty($vehiculos)): ?>
                <div class="alert-error">
                    Esta sucursal no tiene chofer y/o vehículo activo disponible. No se puede crear un viaje desde acá.
                </div>
            <?php else: ?>

            <div class="form-group">
                <label>Chofer *</label>
                <select name="legajo_chofer" required>
                    <option value="">— Seleccioná un chofer —</option>
                    <?php foreach ($choferes as $c): ?>
                        <option value="<?= htmlspecialchars($c['legajo_chofer']) ?>"
                            <?= ($_POST['legajo_chofer'] ?? '') === $c['legajo_chofer'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre']) ?> (<?= $c['legajo_chofer'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Sucursal destino *</label>
                <select name="id_suc_destino" required>
                    <option value="">— Seleccioná una sucursal —</option>
                    <?php foreach ($sucursales as $s): ?>
                        <?php if ($s['id_sucursal'] == $id_suc_origen) continue; ?>
                        <option value="<?= $s['id_sucursal'] ?>"
                            <?= ($_POST['id_suc_destino'] ?? '') == $s['id_sucursal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Vehículo *</label>
                <select name="patente" required>
                    <option value="">Seleccioná un vehículo...</option>
                    <?php foreach ($vehiculos as $v): ?>
                    <option value="<?= htmlspecialchars($v['patente']) ?>"
                        <?= ($_POST['patente'] ?? '') === $v['patente'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['patente']) ?> — <?= htmlspecialchars($v['tipo']) ?> (<?= $v['capacidad_kg_max'] ?> kg)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Fecha y hora de salida *</label>
                    <input type="datetime-local" name="fecha_salida"
                           value="<?= htmlspecialchars($_POST['fecha_salida'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Llegada estimada *</label>
                    <input type="datetime-local" name="fecha_llegada_est"
                           value="<?= htmlspecialchars($_POST['fecha_llegada_est'] ?? '') ?>" required>
                </div>
            </div>

            <?php if (!empty($envios_sin_viaje)): ?>
            <div class="form-group">
                <label>Envíos a incluir (opcional)</label>
                <div class="envios-list">
                    <?php foreach ($envios_sin_viaje as $e): ?>
                    <label class="envio-item">
                        <input type="checkbox" name="envios[]" value="<?= htmlspecialchars($e['nro_tracking']) ?>"
                            <?= in_array($e['nro_tracking'], $_POST['envios'] ?? []) ? 'checked' : '' ?>>
                        <span>
                            <strong style="font-family:monospace;"><?= htmlspecialchars($e['nro_tracking']) ?></strong>
                            — <?= htmlspecialchars($e['ape_rem'] . ', ' . $e['nom_rem']) ?>
                            <?= $e['suc_dest'] ? '→ ' . htmlspecialchars($e['suc_dest']) : '' ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div style="font-size:11px;color:var(--text-soft);margin-top:4px;">
                    Solo se muestran envíos confirmados en depósito origen (estado 1) sin viaje asignado, con origen en esta sucursal.
                </div>
            </div>
            <?php else: ?>
            <div style="font-size:12px;color:var(--text-soft);margin-bottom:16px;">
                No hay envíos pendientes de viaje en esta sucursal.
            </div>
            <?php endif; ?>

            <button type="submit" class="btn-submit">Crear viaje</button>
            <?php endif; ?>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
