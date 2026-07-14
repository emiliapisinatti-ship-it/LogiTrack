<?php
// vars: $registros, $total_rows, $total_pags, $pagina, $filtro_tabla, $filtro_accion, $tablas
$page_subtitle = 'Auditoria';
$nav_links = [['href' => '/admin/index.php', 'label' => '← Panel']];
$extra_css = '
    body { background:linear-gradient(145deg,#f0f0ff 0%,#eef2ff 100%); min-height:100vh; }
    .lt-container { max-width:1000px; }
    .filtros-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:20px; align-items:flex-end; }
    .filtros-bar select { padding:8px 12px; border:1.5px solid var(--border); border-radius:9px; font-size:13px; background:var(--white); }
    .filtros-bar button { padding:8px 18px; background:var(--rose-dark); color:#fff; border:none; border-radius:9px; font-weight:600; cursor:pointer; }
    table { width:100%; border-collapse:collapse; font-size:13px; }
    thead th { text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-soft); padding:9px 12px; border-bottom:1.5px solid var(--border); }
    tbody tr { border-bottom:1px solid var(--nude-dark); }
    tbody tr:hover { background:var(--nude); }
    tbody td { padding:10px 12px; vertical-align:top; font-size:12px; }
    .badge-accion { display:inline-block; padding:3px 10px; border-radius:8px; font-size:11px; font-weight:700; }
    .badge-INSERT { background:#e8f8ee; color:#3a9060; }
    .badge-UPDATE { background:#e8effe; color:#5b7bd6; }
    .badge-DELETE { background:#fee2e2; color:#dc2626; }
    .cambio-item { margin-bottom:3px; font-size:12px; }
    .cambio-campo { color:var(--text-soft); font-weight:600; }
    .cambio-viejo { color:#b03030; text-decoration:line-through; }
    .cambio-nuevo { color:#3a9060; font-weight:600; }
    .cambio-val   { color:var(--text); }
    .paginacion { display:flex; gap:8px; margin-top:16px; justify-content:center; flex-wrap:wrap; }
    .paginacion a, .paginacion span { padding:6px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:13px; text-decoration:none; color:var(--text); }
    .paginacion .activo { background:var(--rose-dark); color:#fff; border-color:var(--rose-dark); }
';
require_once __DIR__ . '/../layouts/header.php';

$tablas_es = [
    'envio'           => 'Envío',
    'usuario'         => 'Usuario',
    'historialestado' => 'Estado de envío',
    'viaje'           => 'Viaje',
    'incidente'       => 'Incidente',
    'sucursal'        => 'Sucursal',
    'vehiculo'        => 'Vehículo',
    'cliente'         => 'Cliente',
    'empleado'        => 'Empleado',
];
$acciones_es = [
    'INSERT' => 'Alta',
    'UPDATE' => 'Modificación',
    'DELETE' => 'Eliminación',
];
$campos_es = [
    'nombre'            => 'Nombre',
    'apellido'          => 'Apellido',
    'estado'            => 'Estado',
    'activo'            => 'Activo',
    'direccion'         => 'Dirección',
    'username'          => 'Usuario',
    'password_hash'     => 'Contraseña',
    'id_sucursal'       => 'Sucursal',
    'telefono'          => 'Teléfono',
    'email'             => 'Email',
    'legajo_chofer'     => 'Chofer',
    'patente'           => 'Patente',
    'id_suc_origen'     => 'Sucursal origen',
    'id_suc_destino'    => 'Sucursal destino',
    'fecha_llegada_est' => 'Llegada estimada',
    'fecha_llegada_real'=> 'Llegada real',
    'descripcion'       => 'Descripción',
    'id_tipo_inc'       => 'Tipo de incidente',
    'id_estado'         => 'Estado envío',
    'cancelado'         => 'Cancelado',
    'dni'               => 'DNI',
    'direccion_entrega' => 'Dirección entrega',
];

function formatearValor(string $campo, $val): string {
    if ($val === null || $val === '') return '—';
    if ($campo === 'activo' || $campo === 'cancelado') return $val ? 'Sí' : 'No';
    if ($campo === 'estado') return match($val) { '1', 1 => 'Activo', '0', 0 => 'Inactivo', default => htmlspecialchars((string)$val) };
    if ($campo === 'password_hash') return '••••••';
    return htmlspecialchars((string)$val);
}

function renderCambio(string $accion, ?string $json_viejo, ?string $json_nuevo, array $campos_es): string {
    $viejo = $json_viejo ? json_decode($json_viejo, true) : [];
    $nuevo = $json_nuevo ? json_decode($json_nuevo, true) : [];
    if (!$viejo) $viejo = [];
    if (!$nuevo) $nuevo = [];
    $html  = '';

    if ($accion === 'INSERT') {
        foreach ($nuevo as $campo => $val) {
            $label = $campos_es[$campo] ?? $campo;
            $html .= '<div class="cambio-item"><span class="cambio-campo">' . htmlspecialchars($label) . ':</span> '
                   . '<span class="cambio-nuevo">' . formatearValor($campo, $val) . '</span></div>';
        }
    } elseif ($accion === 'DELETE') {
        foreach ($viejo as $campo => $val) {
            $label = $campos_es[$campo] ?? $campo;
            $html .= '<div class="cambio-item"><span class="cambio-campo">' . htmlspecialchars($label) . ':</span> '
                   . '<span class="cambio-viejo">' . formatearValor($campo, $val) . '</span></div>';
        }
    } elseif ($accion === 'UPDATE') {
        $all_keys = array_unique(array_merge(array_keys($viejo), array_keys($nuevo)));
        foreach ($all_keys as $campo) {
            $v_old = $viejo[$campo] ?? null;
            $v_new = $nuevo[$campo] ?? null;
            if ($v_old == $v_new) continue;
            $label = $campos_es[$campo] ?? $campo;
            $html .= '<div class="cambio-item"><span class="cambio-campo">' . htmlspecialchars($label) . ':</span> '
                   . '<span class="cambio-viejo">' . formatearValor($campo, $v_old) . '</span>'
                   . ' → <span class="cambio-nuevo">' . formatearValor($campo, $v_new) . '</span></div>';
        }
        if (!$html) $html = '<span style="color:var(--text-soft);font-size:11px;">Sin cambios registrados</span>';
    }

    return $html ?: '<span style="color:var(--text-soft);font-size:11px;">—</span>';
}
?>
<div class="lt-container">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:22px;">Auditoría</div>
            <div style="color:var(--text-soft);font-size:13px;margin-top:3px;"><?= $total_rows ?> registros de cambios</div>
        </div>
    </div>

    <form method="get" action="/admin/router.php" class="filtros-bar">
        <input type="hidden" name="pagina" value="auditoria">
        <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>"
               placeholder="Buscar por ID, sección, usuario..."
               style="flex:1;min-width:200px;padding:8px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-family:inherit;">
        <select name="tabla">
            <option value="">Todas las secciones</option>
            <?php foreach ($tablas as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= $filtro_tabla === $t ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tablas_es[$t] ?? ucfirst($t)) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="accion">
            <option value="">Todos los tipos</option>
            <option value="INSERT" <?= $filtro_accion==='INSERT'?'selected':'' ?>>Alta (nuevo registro)</option>
            <option value="UPDATE" <?= $filtro_accion==='UPDATE'?'selected':'' ?>>Modificación</option>
            <option value="DELETE" <?= $filtro_accion==='DELETE'?'selected':'' ?>>Eliminación</option>
        </select>
        <button type="submit">Filtrar</button>
        <?php if ($filtro_tabla || $filtro_accion || $busqueda): ?>
            <a href="/admin/router.php?pagina=auditoria" style="padding:8px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;color:var(--text-soft);text-decoration:none;">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="lt-card" style="padding:0;overflow:hidden;">
        <?php if (empty($registros)): ?>
            <div style="text-align:center;padding:48px;color:var(--text-soft);">
                <div style="font-size:36px;margin-bottom:8px;">&#128196;</div>
                <em>Sin registros de auditoría todavía.</em>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th style="width:130px;">Fecha y hora</th>
                    <th style="width:110px;">Sección</th>
                    <th style="width:90px;">ID / Código</th>
                    <th style="width:110px;">Tipo de acción</th>
                    <th style="width:90px;">Realizó</th>
                    <th>¿Qué cambió?</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($registros as $r): ?>
            <tr>
                <td style="white-space:nowrap;color:var(--text-soft);font-size:11px;">
                    <?= date('d/m/Y', strtotime($r['fecha_hora'])) ?><br>
                    <span style="font-weight:600;color:var(--text);"><?= date('H:i:s', strtotime($r['fecha_hora'])) ?></span>
                </td>
                <td style="font-weight:600;color:var(--text);"><?= htmlspecialchars($tablas_es[$r['tabla']] ?? ucfirst($r['tabla'])) ?></td>
                <td style="font-family:monospace;font-size:11px;color:var(--rose-dark);"><?= htmlspecialchars($r['id_registro']) ?></td>
                <td><span class="badge-accion badge-<?= htmlspecialchars($r['accion']) ?>"><?= $acciones_es[$r['accion']] ?? $r['accion'] ?></span></td>
                <td style="font-size:12px;"><?= htmlspecialchars($r['username'] ?? '—') ?></td>
                <td><?= renderCambio($r['accion'], $r['datos_viejos'] ?? null, $r['datos_nuevos'] ?? null, $campos_es) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <?php if ($total_pags > 1): ?>
    <div class="paginacion">
        <?php for ($i = 1; $i <= $total_pags; $i++): ?>
            <a href="?pagina=auditoria&p=<?= $i ?>&tabla=<?= urlencode($filtro_tabla) ?>&accion=<?= urlencode($filtro_accion) ?>&q=<?= urlencode($busqueda) ?>"
               class="<?= $i === $pagina ? 'activo' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
