<?php
// vars: $envio, $sucursales, $error
$page_subtitle = 'Editar Envío';
$nav_links = [
    ['href' => '/admin/router.php?pagina=envios', 'label' => '← Envíos'],
    ['href' => '/admin/router.php?pagina=gestionar_envios&tracking='.urlencode($envio['nro_tracking']), 'label' => 'Gestionar estado'],
];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 620px; }
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
    .info-readonly { background: var(--nude); border-radius: 8px; padding: 10px 14px;
                     font-size: 13px; color: var(--text-soft); margin-bottom: 16px; }
    .info-readonly strong { color: var(--text); }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);margin-bottom:4px;">
        Editar envío
    </div>
    <div style="font-family:monospace;font-size:15px;font-weight:700;color:var(--rose-dark);margin-bottom:20px;">
        <?= htmlspecialchars($envio['nro_tracking']) ?>
    </div>

    <?php if ($error): ?>
        <div class="msg-error" style="margin-bottom:16px;">&#9888; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="lt-card">

        <!-- Info de solo lectura -->
        <div class="info-readonly grid-2" style="margin-bottom:20px;">
            <div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:3px;">Remitente</div>
                <strong><?= htmlspecialchars($envio['dni_remitente'] ?? '—') ?></strong>
            </div>
            <div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:3px;">Destinatario</div>
                <strong><?= htmlspecialchars($envio['dni_destinatario'] ?? '—') ?></strong>
            </div>
            <div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:3px;">Fecha de creación</div>
                <strong><?= date('d/m/Y', strtotime($envio['fecha_creacion'])) ?></strong>
            </div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Sucursal destino</label>
                <select name="id_suc_destino">
                    <option value="">Sin sucursal destino (entrega a domicilio)</option>
                    <?php foreach ($sucursales as $s): ?>
                    <option value="<?= $s['id_sucursal'] ?>"
                        <?= $envio['id_suc_destino'] == $s['id_sucursal'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div style="font-size:11px;color:var(--text-soft);margin-top:4px;">Dejá vacío si la entrega es a domicilio</div>
            </div>

            <div class="form-group">
                <label>Dirección de entrega</label>
                <input type="text" name="direccion_entrega"
                       value="<?= htmlspecialchars($envio['direccion_entrega'] ?? '') ?>"
                       placeholder="Ej: Av. Corrientes 1234, CABA">
                <div style="font-size:11px;color:var(--text-soft);margin-top:4px;">Requerido si no hay sucursal destino</div>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn-submit">Guardar cambios</button>
                <a href="/admin/router.php?pagina=gestionar_envios&tracking=<?= urlencode($envio['nro_tracking']) ?>" class="btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
