<?php
// vars: $error, $success, $sucursales, $tipos, $dni_remitente, $dest_existe, $tiene_telefono, $tiene_email
$page_subtitle = 'Enviar Paquete';
$nav_links = [['href' => '/cliente/index.php', 'label' => '← Panel']];
$modalidad_actual = $_POST['modalidad'] ?? 'sucursal';
$extra_css = '
    body { background: linear-gradient(145deg, #f0f0ff 0%, #eef2ff 100%); min-height: 100vh; }

    /* ── Radios funcionales: invisibles pero activos ── */
    #r-suc, #r-dom {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 1px;
        height: 1px;
    }

    .modalidad-selector { display: flex; gap: 12px; margin-bottom: 16px; }
    .modal-option {
        flex: 1; border: 2px solid var(--border); border-radius: 14px;
        padding: 14px; text-align: center; cursor: pointer;
        transition: border-color 0.2s, background 0.2s; background: var(--white);
        display: block;
    }
    .modal-option .modal-icon  { font-size: 26px; margin-bottom: 5px; }
    .modal-option .modal-label { font-size: 13px; font-weight: 600; color: var(--text); }
    .modal-option .modal-desc  { font-size: 11px; color: var(--text-soft); margin-top: 3px; }

    /* Resaltar la opción activa via CSS :checked */
    #r-suc:checked ~ .modalidad-selector label[for="r-suc"],
    #r-dom:checked ~ .modalidad-selector label[for="r-dom"] {
        border-color: var(--rose-dark);
        background: var(--nude);
    }

    /* Por defecto: mostrar sucursal, ocultar dirección */
    .campo-dir { display: none; }

    /* Al elegir domicilio: intercambiar */
    #r-dom:checked ~ .campo-suc-destino { display: none; }
    #r-dom:checked ~ .campo-dir         { display: block; }

    .paquete-item { border: 1.5px solid var(--border); border-radius: 12px;
                    padding: 14px; margin-bottom: 12px; background: var(--white); }
    .paquete-titulo { font-size: 13px; font-weight: 600; color: var(--text-soft);
                      margin-bottom: 10px; }
    .nota-paquetes { font-size: 12px; color: var(--text-soft);
                     background: var(--nude); border-radius: 8px;
                     padding: 8px 12px; margin-bottom: 14px; }
';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="lt-container">
    <div class="lt-card">
        <div class="lt-card-title">Nuevo envío</div>
        <div class="lt-card-sub">Completá los datos para crear tu envío</div>

        <?php if ($error): ?>
            <div class="msg-error">&#9888; <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="msg-success">&#10003; <?= $success ?><br><br>
                <a href="/cliente/router.php?pagina=rastrear" style="color:#3a9060">Rastrear este envío &rarr;</a>
            </div>
        <?php endif; ?>

        <form method="post">

            <!-- Radios funcionales: deben ir PRIMERO dentro del form para que el CSS :checked funcione -->
            <input type="radio" id="r-suc" name="modalidad" value="sucursal"
                   <?= $modalidad_actual === 'sucursal' ? 'checked' : '' ?>>
            <input type="radio" id="r-dom" name="modalidad" value="domicilio"
                   <?= $modalidad_actual === 'domicilio' ? 'checked' : '' ?>>

            <!-- Remitente -->
            <div class="lt-section-label">Remitente</div>
            <div class="remitente-info">
                Tu DNI: <strong><?= htmlspecialchars($dni_remitente ?? 'No asignado') ?></strong>
                &nbsp;·&nbsp; Usuario: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
            </div>

            <!-- Destinatario -->
            <div class="lt-section-label">Destinatario</div>
            <div class="form-group">
                <label>DNI del destinatario *</label>
                <input type="text" name="dni_destinatario"
                       value="<?= htmlspecialchars($_POST['dni_destinatario'] ?? '') ?>"
                       placeholder="DNI de quien recibe" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="nombre_destinatario"
                           value="<?= htmlspecialchars($_POST['nombre_destinatario'] ?? '') ?>"
                           placeholder="Juan" required>
                </div>
                <div class="form-group">
                    <label>Apellido *</label>
                    <input type="text" name="apellido_destinatario"
                           value="<?= htmlspecialchars($_POST['apellido_destinatario'] ?? '') ?>"
                           placeholder="Pérez" required>
                </div>
            </div>
            <?php if ($tiene_telefono || $tiene_email): ?>
            <div class="form-row">
                <?php if ($tiene_telefono): ?>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono_destinatario"
                           value="<?= htmlspecialchars($_POST['telefono_destinatario'] ?? '') ?>"
                           placeholder="Ej: 11 1234-5678">
                </div>
                <?php endif; ?>
                <?php if ($tiene_email): ?>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email_destinatario"
                           value="<?= htmlspecialchars($_POST['email_destinatario'] ?? '') ?>"
                           placeholder="juan@correo.com">
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Modalidad de entrega (labels visuales; los radios funcionales están arriba) -->
            <div class="lt-section-label">Modalidad de entrega</div>
            <div class="modalidad-selector">
                <label for="r-suc" class="modal-option">
                    <div class="modal-icon">&#127962;</div>
                    <div class="modal-label">Retirar en sucursal</div>
                    <div class="modal-desc">El destinatario retira en una sucursal</div>
                </label>
                <label for="r-dom" class="modal-option">
                    <div class="modal-icon">&#127968;</div>
                    <div class="modal-label">Entrega a domicilio</div>
                    <div class="modal-desc">Se entrega en la dirección indicada</div>
                </label>
            </div>

            <!-- Sucursal destino (visible en modo sucursal) -->
            <div class="form-group campo-suc-destino">
                <label>Sucursal destino *</label>
                <select name="id_suc_destino">
                    <option value="">Seleccioná una sucursal...</option>
                    <?php foreach ($sucursales as $suc): ?>
                    <option value="<?= $suc['id_sucursal'] ?>"
                        <?= (isset($_POST['id_suc_destino']) && $_POST['id_suc_destino'] == $suc['id_sucursal']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($suc['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dirección entrega (visible en modo domicilio) -->
            <div class="form-group campo-dir">
                <label>Dirección de entrega *</label>
                <input type="text" name="direccion_entrega"
                       value="<?= htmlspecialchars($_POST['direccion_entrega'] ?? '') ?>"
                       placeholder="Calle, número, ciudad">
            </div>

            <!-- Sucursal origen (siempre visible) -->
            <div class="lt-section-label">Sucursal de origen</div>
            <div class="form-group">
                <label>Sucursal origen *</label>
                <select name="id_suc_origen" required>
                    <option value="">Seleccioná...</option>
                    <?php foreach ($sucursales as $suc): ?>
                    <option value="<?= $suc['id_sucursal'] ?>"
                        <?= (isset($_POST['id_suc_origen']) && $_POST['id_suc_origen'] == $suc['id_sucursal']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($suc['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Paquetes dinámicos -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;margin-bottom:10px;">
                <div class="lt-section-label" style="margin:0;">Paquetes</div>
                <?php if ($n_paquetes < 10): ?>
                <button type="submit" name="agregar_paquete" value="1"
                        style="padding:6px 14px;border:1.5px solid var(--rose-dark);border-radius:8px;background:var(--white);color:var(--rose-dark);font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;">
                    + Agregar paquete
                </button>
                <?php endif; ?>
            </div>
            <input type="hidden" name="n_paquetes" value="<?= $n_paquetes ?>">

            <?php for ($i = 0; $i < $n_paquetes; $i++): ?>
            <div class="paquete-item">
                <div class="paquete-titulo">Paquete <?= $i + 1 ?></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Peso (kg) <?= $i === 0 ? '*' : '' ?></label>
                        <input type="number" name="peso_kg[]" step="0.1" min="0.1"
                               placeholder="2.5" <?= $i === 0 ? 'required' : '' ?>
                               value="<?= htmlspecialchars($_POST['peso_kg'][$i] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Tipo de contenido</label>
                        <select name="id_tipo_cont[]">
                            <option value="">Sin especificar</option>
                            <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= $tipo['id_tipo_cont'] ?>"
                                <?= (isset($_POST['id_tipo_cont'][$i]) && $_POST['id_tipo_cont'][$i] == $tipo['id_tipo_cont']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Alto (cm)</label>
                        <input type="number" name="alto_cm[]" min="1" placeholder="30"
                               value="<?= htmlspecialchars($_POST['alto_cm'][$i] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Ancho (cm)</label>
                        <input type="number" name="ancho_cm[]" min="1" placeholder="20"
                               value="<?= htmlspecialchars($_POST['ancho_cm'][$i] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Largo (cm)</label>
                        <input type="number" name="largo_cm[]" min="1" placeholder="15"
                               value="<?= htmlspecialchars($_POST['largo_cm'][$i] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción del contenido</label>
                    <textarea name="descripcion[]"
                              placeholder="Ej: Ropa, libros, electrónica..."><?= htmlspecialchars($_POST['descripcion'][$i] ?? '') ?></textarea>
                </div>
            </div>
            <?php endfor; ?>

            <button type="submit" class="btn-primary">Crear envío</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
