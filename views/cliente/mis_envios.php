<?php
// vars: $envios, $dni
$page_subtitle = 'Mis Envios';
$nav_links = [
    ['href' => '/cliente/index.php', 'label' => '← Panel'],
    ['href' => '/cliente/router.php?pagina=enviar',       'label' => '📦 Nuevo envío'],
];
$extra_css = '';
require_once __DIR__ . '/../layouts/header.php';
?>
<style>
.lt-container { max-width: 1100px; }
table { width:100%; border-collapse:collapse; font-size:14px; }
thead th { text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-soft); padding:10px 14px; border-bottom:1.5px solid var(--border); }
tbody tr { border-bottom:1px solid var(--nude-dark); transition:background 0.15s; }
tbody tr:hover { background:var(--nude); }
tbody td { padding:12px 14px; vertical-align:middle; }
.tracking-mono { font-family:monospace; font-weight:700; font-size:13px; color:var(--rose-dark); }
.badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; background:var(--nude-dark); color:var(--text-soft); }
.badge-entregado { background:#e8f8ee; color:#3a9060; }
.badge-cancelado { background:#fde8e8; color:#b03030; }
.btn-sm { padding:5px 12px; border-radius:8px; font-size:12px; font-weight:600; border:1.5px solid var(--border); background:var(--white); color:var(--text-soft); text-decoration:none; transition:all 0.2s; }
.btn-sm:hover { border-color:var(--rose); color:var(--rose-dark); }
</style>

<div class="lt-container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);">Mis envios</div>
            <div style="color:var(--text-soft);font-size:13px;margin-top:3px;"><?= count($envios) ?> envio<?= count($envios) != 1 ? 's' : '' ?> registrados</div>
        </div>
        <a href="/cliente/router.php?pagina=enviar" class="btn-sm" style="border-color:var(--rose);color:var(--rose-dark);">+ Nuevo envio</a>
    </div>

    <?php if (isset($_GET['confirmar_cancelar'])): ?>
    <div class="lt-card" style="border:1.5px solid #f5a0a0;margin-bottom:20px;">
        <div style="font-weight:700;color:#b03030;margin-bottom:6px;">&#9888; Confirmar cancelación</div>
        <div style="font-size:13px;margin-bottom:14px;">
            ¿Cancelar el envío <strong><?= htmlspecialchars($_GET['confirmar_cancelar']) ?></strong>?
        </div>
        <form method="POST" style="display:flex;gap:10px;">
            <input type="hidden" name="cancelar" value="<?= htmlspecialchars($_GET['confirmar_cancelar']) ?>">
            <button type="submit" style="padding:8px 16px;background:#b03030;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Sí, cancelar</button>
            <a href="/cliente/router.php?pagina=mis_envios" class="btn-sm">Volver</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="lt-card" style="padding:0;overflow:hidden;">
        <?php if (empty($envios)): ?>
            <div style="text-align:center;padding:48px;color:var(--text-soft);">
                <div style="font-size:40px;margin-bottom:10px;">&#128230;</div>
                <em>Todavia no tenes envios registrados.</em><br>
                <a href="/cliente/router.php?pagina=enviar" class="btn-sm" style="margin-top:14px;display:inline-block;">Enviar primer paquete</a>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Tracking</th>
                    <th>Tipo</th>
                    <th>Contacto</th>
                    <th>Destino</th>
                    <th>Paquetes</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php
            $id_inicial = $id_estado_inicial;
            foreach ($envios as $e):
                $es_enviado = $e['dni_remitente'] === $dni;
                $est = strtolower($e['estado_actual'] ?? '');
                $badge = str_contains($est,'entregado') ? 'badge-entregado' : (str_contains($est,'nulad') || str_contains($est,'ancelad') ? 'badge-cancelado' : '');
                $cancelable = $es_enviado && (is_null($e['id_estado_actual']) || (int)$e['id_estado_actual'] === (int)$id_inicial);
                if ($es_enviado) {
                    $tipo_label = '<span style="color:var(--rose-dark);font-weight:600;">📤 Enviado</span>';
                    $contacto   = htmlspecialchars(($e['apellido_dest'] ?? '') . ', ' . ($e['nombre_dest'] ?? ''));
                } else {
                    $tipo_label = '<span style="color:#3a9060;font-weight:600;">📥 Recibido</span>';
                    $contacto   = 'De: ' . htmlspecialchars(($e['apellido_remit'] ?? '') . ', ' . ($e['nombre_remit'] ?? ''));
                }
            ?>
                <tr>
                    <td><span class="tracking-mono"><?= htmlspecialchars($e['nro_tracking']) ?></span></td>
                    <td><?= $tipo_label ?></td>
                    <td style="font-size:13px;"><?= $contacto ?></td>
                    <td style="font-size:13px;">
                        <?= $e['suc_destino'] ? '🏢 '.htmlspecialchars($e['suc_destino']) : '🏠 '.htmlspecialchars($e['direccion_entrega'] ?? '-') ?>
                    </td>
                    <td style="font-size:13px;text-align:center;">📦 <?= (int)($e['cant_paquetes'] ?? 1) ?></td>
                    <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($e['estado_actual'] ?? 'Sin estado') ?></span></td>
                    <td style="font-size:13px;color:var(--text-soft);"><?= date('d/m/Y', strtotime($e['fecha_creacion'])) ?></td>
                    <td style="display:flex;gap:6px;white-space:nowrap;">
                        <a href="/cliente/router.php?pagina=rastrear&tracking=<?= urlencode($e['nro_tracking']) ?>" class="btn-sm">Rastrear</a>
                        <?php if ($cancelable): ?>
                        <a href="/cliente/router.php?pagina=mis_envios&confirmar_cancelar=<?= urlencode($e['nro_tracking']) ?>"
                           class="btn-sm" style="border-color:#f5a0a0;color:#b03030;">Cancelar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
