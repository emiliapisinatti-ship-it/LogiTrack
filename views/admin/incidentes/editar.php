<?php
// vars: $incidente, $tipos, $error
$page_subtitle = 'Editar Incidente';
$nav_links = [['href' => '/admin/router.php?pagina=incidentes', 'label' => '← Incidentes']];
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
    .lt-container { max-width: 620px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase;
                        letter-spacing: 0.5px; color: var(--text-soft); margin-bottom: 5px; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 9px;
        font-size: 14px; font-family: inherit; background: #fff; color: var(--text);
        box-sizing: border-box; transition: border-color 0.2s; }
    .form-group textarea { resize: vertical; min-height: 100px; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none; border-color: var(--rose); }
    .btn-submit { padding: 10px 24px; background: var(--rose-dark); color: #fff; border: none;
                  border-radius: 9px; font-size: 14px; font-weight: 700; cursor: pointer;
                  font-family: inherit; transition: opacity 0.2s; }
    .btn-submit:hover { opacity: 0.88; }
    .btn-cancel { padding: 10px 18px; background: #fff; color: var(--text-soft);
                  border: 1.5px solid var(--border); border-radius: 9px; font-size: 14px;
                  font-weight: 600; text-decoration: none; }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">
    <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);margin-bottom:4px;">
        Editar incidente #<?= $incidente['nro_incidente'] ?>
    </div>
    <div style="color:var(--text-soft);font-size:13px;margin-bottom:24px;">
        Viaje <?= htmlspecialchars($incidente['cod_viaje']) ?> — <?= date('d/m/Y H:i', strtotime($incidente['fecha_hora'])) ?>
    </div>

    <?php if ($error): ?>
        <div class="msg-error" style="margin-bottom:16px;">&#9888; <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="lt-card">
        <form method="POST">
            <div class="form-group">
                <label>Tipo de incidente *</label>
                <select name="id_tipo_inc" required>
                    <option value="">Seleccioná un tipo</option>
                    <?php foreach ($tipos as $t): ?>
                    <option value="<?= $t['id_tipo_inc'] ?>"
                        <?= $t['id_tipo_inc'] == $incidente['id_tipo_inc'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Descripción *</label>
                <textarea name="descripcion" placeholder="Descripción detallada del incidente..." required><?= htmlspecialchars($incidente['descripcion']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <option value="abierto"  <?= ($incidente['estado'] ?? 'abierto') === 'abierto'  ? 'selected' : '' ?>>Abierto</option>
                    <option value="cerrado" <?= in_array($incidente['estado'] ?? '', ['cerrado','resuelto']) ? 'selected' : '' ?>>Resuelto</option>
                </select>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px;">
                <button type="submit" class="btn-submit">Guardar cambios</button>
                <a href="/admin/router.php?pagina=incidentes" class="btn-cancel">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
