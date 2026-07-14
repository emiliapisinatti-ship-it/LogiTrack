<?php
// vars: $viaje, $choferes, $vehiculos, $sucursales, $error
$page_subtitle = 'Editar Viaje';
$nav_links = [
    ['href' => '/admin/router.php?pagina=viajes',                              'label' => '← Viajes'],
    ['href' => '/admin/router.php?pagina=ver_viaje&cod='.urlencode($viaje['cod_viaje']), 'label' => 'Ver detalle'],
];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 640px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase;
                        letter-spacing: 0.5px; color: var(--text-soft); margin-bottom: 5px; }
    .form-group select, .form-group input {
        width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 9px;
        font-size: 14px; font-family: inherit; background: #fff; color: var(--text);
        box-sizing: border-box; transition: border-color 0.2s; }
    .form-group select:focus, .form-group input:focus { outline: none; border-color: var(--rose); }
    .btn-submit { padding: 10px 24px; background: var(--rose-dark); color: #fff; border: none;
                  border-radius: 9px; font-size: 14px; font-weight: 700; cursor: pointer;
                  font-family: inherit; transition: opacity 0.2s; }
    .btn-submit:hover { opacity: 0.88; }
    .btn-cancel { padding: 10px 18px; background: #fff; color: var(--text-soft);
                  border: 1.5px solid var(--border); border-radius: 9px; font-size: 14px;
                  font-weight: 600; text-decoration: none; }
    .info-readonly { background: var(--nude); border-radius: 8px; padding: 8px 12px;
                     font-size: 13px; color: var(--text-soft); margin-bottom: 16px; }
    .info-readonly strong { color: var(--text); }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);margin-bottom:4px;">
        Editar viaje <span style="font-family:monospace;color:var(--rose-dark);"><?= htmlspecialchars($viaje['cod_viaje']) ?></span>
    </div>
    <div style="color:var(--text-soft);font-size:13px;margin-bottom:24px;">
        Salida: <?= date('d/m/Y H:i', strtotime($viaje['fecha_salida'])) ?>
    </div>

    <?php if ($error): ?>
        <div class="msg-error" style="margin-bottom:16px;">&#9888; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="lt-card">
        <form method="POST">
            <div class="info-readonly">
                Sucursal origen: <strong><?= htmlspecialchars($viaje['suc_origen'] ?? '') ?></strong>
                (no editable — el chofer y el vehículo deben pertenecer a esta sucursal)
            </div>

            <?php if (empty($choferes) || empty($vehiculos)): ?>
                <div class="msg-error" style="margin-bottom:16px;">
                    Esta sucursal no tiene chofer y/o vehículo activo disponible.
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Chofer *</label>
                <select name="legajo_chofer" required>
                    <option value="">Seleccioná un chofer</option>
                    <?php foreach ($choferes as $c): ?>
                    <option value="<?= htmlspecialchars($c['legajo_chofer']) ?>"
                        <?= $c['legajo_chofer'] === $viaje['legajo_chofer'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre']) ?> (<?= htmlspecialchars($c['legajo_chofer']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Vehículo (patente) *</label>
                <select name="patente" required>
                    <option value="">Seleccioná un vehículo</option>
                    <?php foreach ($vehiculos as $v): ?>
                    <option value="<?= htmlspecialchars($v['patente']) ?>"
                        <?= $v['patente'] === $viaje['patente'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['patente']) ?> — <?= htmlspecialchars($v['tipo']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Sucursal destino *</label>
                <select name="id_suc_destino" required>
                    <option value="">Seleccioná una sucursal</option>
                    <?php foreach ($sucursales as $s): ?>
                        <?php if ($s['id_sucursal'] == $viaje['id_suc_origen']) continue; ?>
                        <option value="<?= $s['id_sucursal'] ?>"
                            <?= $s['id_sucursal'] == ($viaje['id_suc_destino'] ?? 0) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Fecha de llegada estimada *</label>
                <input type="datetime-local" name="fecha_llegada_est"
                       value="<?= date('Y-m-d\TH:i', strtotime($viaje['fecha_llegada_est'])) ?>" required>
            </div>

            <div class="info-readonly">
                Fecha de salida: <strong><?= date('d/m/Y H:i', strtotime($viaje['fecha_salida'])) ?></strong>
                (no editable — para cancelar el viaje usá el listado)
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn-submit">Guardar cambios</button>
                <a href="/admin/router.php?pagina=ver_viaje&cod=<?= urlencode($viaje['cod_viaje']) ?>" class="btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
