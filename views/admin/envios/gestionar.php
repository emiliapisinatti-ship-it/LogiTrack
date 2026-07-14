<?php
// vars: $error, $success, $envio, $historial, $estados, $tracking_buscado
$page_subtitle = 'Gestionar Envio';
$nav_links = [
    ['href' => '/admin/router.php?pagina=envios', 'label' => '← Listado'],
];
$extra_css = '
    body { background:linear-gradient(145deg,#f0f0ff 0%,#eef2ff 100%); min-height:100vh; }
    .lt-container { max-width:800px; }
    .search-bar { display:flex; gap:10px; margin-bottom:20px; }
    .search-bar input { flex:1; padding:10px 14px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; }
    .search-bar button { padding:10px 20px; background:var(--rose-dark); color:#fff; border:none; border-radius:10px; font-weight:600; cursor:pointer; }
    .envio-detalle { padding:20px; }
    .field-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px; }
    .field-item label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--text-soft); }
    .field-item span { font-size:14px; font-weight:500; }
    .historial-entry { padding:8px 0; border-bottom:1px solid var(--nude-dark); font-size:13px; }
    .historial-entry:last-child { border-bottom:none; }
    .estado-change-form { margin-top:20px; padding-top:16px; border-top:1.5px solid var(--border); }
    .form-inline { display:grid; grid-template-columns:1fr 1fr auto; gap:10px; align-items:end; }
    select, textarea { padding:9px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:14px; background:var(--white); font-family:inherit; }
    .btn-sm-rose { padding:9px 18px; background:var(--rose-dark); color:#fff; border:none; border-radius:9px; font-weight:600; cursor:pointer; }
    .msg-error   { background:#fde8e8; border:1.5px solid #f5a0a0; color:#b03030; border-radius:9px; padding:10px 14px; margin-bottom:14px; font-size:13px; }
    .msg-success { background:#e8f8ee; border:1.5px solid #a0d0b0; color:#3a6050; border-radius:9px; padding:10px 14px; margin-bottom:14px; font-size:13px; }
    .tracking-mono { font-family:monospace; font-weight:700; color:var(--rose-dark); }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
<div class="lt-container">

    <!-- Buscador -->
    <div class="lt-card">
        <div class="lt-card-title">🔍 Buscar envío</div>
        <form method="post" class="search-bar">
            <input type="text" name="tracking_buscar"
                   placeholder="Número de tracking (ej: TRK100772905)"
                   value="<?= htmlspecialchars($tracking_buscado) ?>">
            <button type="submit">Buscar</button>
        </form>

        <?php if ($error): ?>
            <div class="msg-error">⚠️ <?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
    </div>

    <?php if ($envio): ?>

    <!-- Datos del envío -->
    <div class="lt-card" style="margin-top:20px;">
        <div class="envio-header">
            <div>
                <div class="lt-card-title" style="margin-bottom:4px;">Datos del envío</div>
                <span class="tracking-code"><?= htmlspecialchars($envio['nro_tracking']) ?></span>
            </div>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <label>Remitente</label>
                <span><?= htmlspecialchars($envio['apellido_remitente'] . ', ' . $envio['nombre_remitente']) ?></span>
            </div>
            <div class="info-item">
                <label>Destinatario</label>
                <span><?= htmlspecialchars($envio['apellido_dest'] . ', ' . $envio['nombre_dest']) ?></span>
            </div>
            <div class="info-item">
                <label>Sucursal origen</label>
                <span><?= htmlspecialchars($envio['suc_origen'] ?? '—') ?></span>
            </div>
            <div class="info-item">
                <label>Destino</label>
                <span>
                    <?php if ($envio['suc_destino']): ?>
                        🏢 <?= htmlspecialchars($envio['suc_destino']) ?>
                    <?php elseif ($envio['direccion_entrega']): ?>
                        🏠 <?= htmlspecialchars($envio['direccion_entrega']) ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-item">
                <label>Fecha creación</label>
                <span><?= date('d/m/Y H:i', strtotime($envio['fecha_creacion'])) ?></span>
            </div>
        </div>
    </div>

    <?php if ($_SESSION['id_rol'] == 1 && !$es_estado_final): ?>
    <!-- Cambiar sucursal: solo admin -->
    <div class="lt-card" style="margin-top:20px;">
        <div class="lt-card-title">🏢 Cambiar sucursal del envío</div>
        <div class="lt-card-sub">Reasignar la sucursal de origen o de destino</div>
        <form method="post">
            <input type="hidden" name="accion"       value="cambiar_sucursal">
            <input type="hidden" name="nro_tracking" value="<?= htmlspecialchars($envio['nro_tracking']) ?>">
            <input type="hidden" name="tracking_buscar" value="<?= htmlspecialchars($envio['nro_tracking']) ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>¿Qué sucursal cambiar? *</label>
                    <select name="tipo_sucursal" required>
                        <option value="origen"  <?= !$envio['suc_destino'] ? 'selected' : '' ?>>Sucursal origen</option>
                        <option value="destino" <?=  $envio['suc_destino'] ? 'selected' : '' ?>>Sucursal destino</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nueva sucursal *</label>
                    <select name="id_sucursal_nueva" required>
                        <option value="">Seleccioná...</option>
                        <?php foreach ($sucursales as $s): ?>
                        <option value="<?= $s['id_sucursal'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary" style="background:#5a7fa0;">Cambiar sucursal</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Actualizar estado -->
    <div class="lt-card" style="margin-top:20px;">
        <div class="lt-card-title">🔄 Actualizar estado</div>
        <div class="lt-card-sub">Registrá un nuevo movimiento en el historial del envío</div>

        <?php if (!$puede_cambiar_estado): ?>
            <div class="msg-error">🔒 <?= htmlspecialchars($error_acceso) ?></div>
        <?php else: ?>
        <form method="post">
            <input type="hidden" name="accion" value="cambiar_estado">
            <input type="hidden" name="nro_tracking" value="<?= htmlspecialchars($envio['nro_tracking']) ?>">
            <input type="hidden" name="tracking_buscar" value="<?= htmlspecialchars($envio['nro_tracking']) ?>">
            <div class="form-row">
                <div class="form-group">
                    <label>Nuevo estado *</label>
                    <select name="id_estado" required>
                        <option value="">Seleccioná...</option>
                        <?php foreach ($estados as $e): ?>
                        <option value="<?= $e['id_estado'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Observación (opcional)</label>
                <input type="text" name="observacion" placeholder="Ej: Cliente retiró en sucursal">
            </div>
            <button type="submit" class="btn-primary">Registrar cambio de estado</button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Historial de estados -->
    <div class="lt-card" style="margin-top:20px;">
        <div class="lt-card-title">📋 Historial de estados</div>
        <?php if (!empty($historial)): ?>
        <div class="timeline">
            <?php foreach ($historial as $i => $h): ?>
            <div class="timeline-item <?= $i === 0 ? 'actual' : '' ?>">
                <div class="tl-dot"></div>
                <div>
                    <div class="tl-estado">
                        <?= htmlspecialchars($h['estado']) ?>
                        <?php if ($i === 0): ?>
                            <span class="badge-actual">actual</span>
                        <?php endif; ?>
                    </div>
                    <div class="tl-meta">
                        <?= date('d/m/Y H:i', strtotime($h['fecha_hora'])) ?>
                        <?php if ($h['username']): ?>
                            · por <strong><?= htmlspecialchars($h['username']) ?></strong>
                        <?php endif; ?>
                    </div>
                    <?php if ($h['observacion']): ?>
                    <div class="tl-obs">"<?= htmlspecialchars($h['observacion']) ?>"</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-timeline">Sin movimientos registrados aún.</div>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
