<?php
// vars: $usuarios, $totales, $filtro
$page_subtitle = 'Usuarios';
$nav_links = [
    ['href' => '/admin/index.php', 'label' => '← Panel'],
    ['href' => '/admin/router.php?pagina=crear_usuario', 'label' => '+ Nuevo usuario', 'class' => 'btn-logout'],
];
$extra_css = '
        body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }
        .lt-container { max-width: 900px; }

        .filtros {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .filtro-btn {
            padding: 8px 18px;
            border-radius: 20px;
            border: 1.5px solid var(--border);
            background: var(--white);
            color: var(--text-soft);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            font-family: \'DM Sans\', sans-serif;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .filtro-btn:hover { border-color: var(--rose); color: var(--rose-dark); }
        .filtro-btn.activo { background: var(--rose-dark); color: white; border-color: var(--rose-dark); }
        .filtro-btn .count {
            background: rgba(255,255,255,0.25);
            border-radius: 10px;
            padding: 1px 7px;
            font-size: 11px;
        }
        .filtro-btn:not(.activo) .count {
            background: var(--nude-dark);
            color: var(--rose-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        thead th {
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-soft);
            padding: 10px 14px;
            border-bottom: 1.5px solid var(--border);
        }
        tbody tr {
            border-bottom: 1px solid var(--nude-dark);
            transition: background 0.15s;
        }
        tbody tr:hover { background: var(--nude); }
        tbody td { padding: 13px 14px; vertical-align: middle; }

        .badge-rol {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-1 { background: #ede9fe; color: #4f46e5; }
        .badge-2 { background: #e8effe; color: #5b7bd6; }
        .badge-3 { background: #e8f8ee; color: #3a9060; }
        .badge-4 { background: #fef6e4; color: #c89040; }

        .badge-estado {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .estado-1 { background: #3a9060; }
        .estado-0 { background: #c06060; }

        .legajo { font-size: 12px; color: var(--text-soft); font-family: monospace; }

        .empty { text-align: center; padding: 48px; color: var(--text-soft); font-size: 14px; }
        .empty .empty-icon { font-size: 40px; margin-bottom: 10px; }

        .btn-sm {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            border: 1.5px solid var(--border);
            background: var(--white);
            color: var(--text-soft);
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-sm:hover { border-color: var(--rose); color: var(--rose-dark); }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
';
require_once __DIR__ . '/../../layouts/header.php';
?>
</header>

<div class="lt-container">
    <a href="/admin/index.php" style="display:inline-flex;align-items:center;gap:6px;color:var(--text-soft);text-decoration:none;font-size:13px;font-weight:500;margin-bottom:20px;">&larr; Volver al panel</a>

    <div class="header-actions">
        <div>
            <div class="lt-card-title" style="font-family:'DM Serif Display',serif;font-size:22px;color:var(--text);">Usuarios del sistema</div>
            <div style="color:var(--text-soft);font-size:13px;margin-top:4px;">Empleados, choferes y clientes registrados</div>
        </div>
        <a href="/admin/router.php?pagina=crear_usuario" class="btn-primary" style="width:auto;padding:10px 20px;text-decoration:none;display:inline-block;">+ Nuevo usuario</a>
    </div>

    <!-- Busqueda -->
    <form method="get" action="/admin/router.php" style="margin-bottom:14px;display:flex;gap:10px;">
        <input type="hidden" name="pagina" value="usuarios">
        <?php if ($filtro > 0): ?><input type="hidden" name="rol" value="<?= $filtro ?>"><?php endif; ?>
        <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>"
               placeholder="Buscar por nombre, usuario, legajo o DNI..."
               style="flex:1;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;font-family:inherit;">
        <button type="submit" style="padding:10px 20px;background:var(--rose-dark);color:#fff;border:none;border-radius:10px;font-weight:600;font-family:inherit;cursor:pointer;">Buscar</button>
        <?php if ($busqueda): ?>
            <a href="/admin/router.php?pagina=usuarios<?= $filtro ? '&rol='.$filtro : '' ?>"
               style="padding:10px 16px;border:1.5px solid var(--border);border-radius:10px;font-size:13px;color:var(--text-soft);text-decoration:none;">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Filtros -->
    <div class="filtros">
        <a href="/admin/router.php?pagina=usuarios" class="filtro-btn <?= $filtro == 0 ? 'activo' : '' ?>">
            Todos <span class="count"><?= $totales['todos'] ?></span>
        </a>
        <a href="/admin/router.php?pagina=usuarios&rol=2" class="filtro-btn <?= $filtro == 2 ? 'activo' : '' ?>">
            🏢 Empleados <span class="count"><?= $totales[2] ?? 0 ?></span>
        </a>
        <a href="/admin/router.php?pagina=usuarios&rol=3" class="filtro-btn <?= $filtro == 3 ? 'activo' : '' ?>">
            🚛 Choferes <span class="count"><?= $totales[3] ?? 0 ?></span>
        </a>
        <a href="/admin/router.php?pagina=usuarios&rol=4" class="filtro-btn <?= $filtro == 4 ? 'activo' : '' ?>">
            👤 Clientes <span class="count"><?= $totales[4] ?? 0 ?></span>
        </a>
    </div>

    <div class="lt-card" style="padding:0;overflow:hidden;">
        <?php if (empty($usuarios)): ?>
            <div class="empty">
                <div class="empty-icon">👥</div>
                No hay usuarios en esta categoría.
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Legajo / DNI</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?></strong>
                    </td>
                    <td style="color:var(--text-soft);">@<?= htmlspecialchars($u['username']) ?></td>
                    <td>
                        <?php
                        $rolMap = ['Administrador'=>1,'Empleado Sucursal'=>2,'Chofer'=>3,'Cliente'=>4];
                        $rolId = $rolMap[$u['rol']] ?? 0;
                        ?>
                        <span class="badge-rol badge-<?= $rolId ?>"><?= htmlspecialchars($u['rol']) ?></span>
                    </td>
                    <td class="legajo">
                        <?php
                        if ($u['legajo']) echo htmlspecialchars($u['legajo']);
                        elseif ($u['dni_cliente']) echo 'DNI: ' . htmlspecialchars($u['dni_cliente']);
                        else echo '—';
                        ?>
                    </td>
                    <td>
                        <span class="badge-estado estado-<?= $u['estado'] ?>"></span>
                        <?= $u['estado'] ? 'Activo' : 'Inactivo' ?>
                    </td>
                    <td style="display:flex;gap:6px;align-items:center;">
                        <a href="/admin/router.php?pagina=ver_usuario&id=<?= $u['id_usuario'] ?>" class="btn-sm">Ver / Editar</a>
                        <a href="/admin/router.php?pagina=ver_usuario&id=<?= $u['id_usuario'] ?>&confirmar_toggle=1"
                           class="btn-sm" style="border-color:<?= $u['estado'] ? '#dc2626' : '#3a9060' ?>;color:<?= $u['estado'] ? '#dc2626' : '#3a9060' ?>;">
                            <?= $u['estado'] ? 'Desactivar' : 'Activar' ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
