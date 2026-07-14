<?php
// vars: $error, $viajes
$back = match($_SESSION['id_rol'] ?? 0) { 1,2,3 => '/admin/router.php?pagina=incidentes', default => '/admin/index.php' };
$page_subtitle = 'Reportar Incidente';
$nav_links = [['href' => $back, 'label' => '← Incidentes']];
$extra_css = '
    body { background:linear-gradient(145deg,#f0f0ff 0%,#eef2ff 100%); min-height:100vh; }
    .lt-container { max-width:600px; }
    .form-group { margin-bottom:16px; }
    .form-group label { display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--text-soft); margin-bottom:6px; }
    .form-group input, .form-group select, .form-group textarea { width:100%; padding:10px 12px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; background:var(--white); color:var(--text); box-sizing:border-box; font-family:inherit; }
    .form-group textarea { resize:vertical; min-height:100px; }
    .btn-submit { width:100%; padding:12px; background:var(--rose-dark); color:#fff; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer; }
    .alert-error { background:#fde8e8; border:1.5px solid #f5a0a0; color:#b03030; border-radius:10px; padding:10px 14px; margin-bottom:16px; font-size:14px; }
    .hint { font-size:11px; color:var(--text-soft); margin-top:4px; }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);margin-bottom:20px;">Reportar incidente</div>

    <?php if ($error): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="lt-card">
        <form method="POST">
            <div class="form-group">
                <label>Tipo de incidente *</label>
                <select name="id_tipo_inc" required>
                    <option value="">— Seleccioná un tipo —</option>
                    <?php foreach ($model->obtenerTipos() as $tipo): ?>
                        <option value="<?= $tipo['id_tipo_inc'] ?>"
                            <?= (int)($_POST['id_tipo_inc'] ?? 0) === (int)$tipo['id_tipo_inc'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Descripción del incidente *</label>
                <textarea name="descripcion" placeholder="Describí qué pasó con el mayor detalle posible..." required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Número de tracking (opcional)</label>
                <input type="text" name="nro_tracking" placeholder="Ej: A1B2C3D4E5"
                       value="<?= htmlspecialchars(strtoupper($_POST['nro_tracking'] ?? '')) ?>"
                       style="text-transform:uppercase;">
                <div class="hint">Si el incidente está relacionado con un paquete en particular.</div>
            </div>

            <?php if (!empty($viajes)): ?>
            <div class="form-group">
                <label>Viaje relacionado (opcional)</label>
                <select name="cod_viaje">
                    <option value="">— Sin viaje asociado —</option>
                    <?php foreach ($viajes as $v): ?>
                        <option value="<?= htmlspecialchars($v['cod_viaje']) ?>"
                            <?= ($_POST['cod_viaje'] ?? '') === $v['cod_viaje'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['cod_viaje']) ?>
                            — <?= htmlspecialchars($v['suc_origen'] ?? '?') ?>
                            (<?= date('d/m H:i', strtotime($v['fecha_salida'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nueva llegada estimada (opcional)</label>
                <input type="datetime-local" name="nueva_fecha_est"
                       value="<?= htmlspecialchars($_POST['nueva_fecha_est'] ?? '') ?>">
                <div class="hint">Completá solo si el incidente genera demora. Actualiza la hora estimada del viaje seleccionado.</div>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn-submit">Enviar reporte</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
