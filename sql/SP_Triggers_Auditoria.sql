DELIMITER $$

-- ============================================================
-- ENVIO
-- ============================================================
DROP TRIGGER IF EXISTS trg_envio_insert $$
CREATE TRIGGER trg_envio_insert
AFTER INSERT ON envio
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, id_usuario, fecha_hora, datos_nuevos)
    VALUES ('envio', NEW.nro_tracking, 'INSERT', NEW.id_usuario, NOW(),
        JSON_OBJECT('nro_tracking', NEW.nro_tracking, 'dni_remitente', NEW.dni_remitente,
                    'dni_destinatario', NEW.dni_destinatario, 'id_suc_origen', NEW.id_suc_origen));
END $$

DROP TRIGGER IF EXISTS trg_envio_update $$
CREATE TRIGGER trg_envio_update
AFTER UPDATE ON envio
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, fecha_hora, datos_viejos, datos_nuevos)
    VALUES ('envio', NEW.nro_tracking, 'UPDATE', NOW(),
        JSON_OBJECT('id_suc_destino', OLD.id_suc_destino, 'direccion_entrega', OLD.direccion_entrega),
        JSON_OBJECT('id_suc_destino', NEW.id_suc_destino, 'direccion_entrega', NEW.direccion_entrega));
END $$

DROP TRIGGER IF EXISTS trg_envio_delete $$
CREATE TRIGGER trg_envio_delete
BEFORE DELETE ON envio
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, fecha_hora, datos_viejos)
    VALUES ('envio', OLD.nro_tracking, 'DELETE', NOW(),
        JSON_OBJECT('nro_tracking', OLD.nro_tracking, 'dni_remitente', OLD.dni_remitente));
END $$

-- ============================================================
-- USUARIO
-- ============================================================
DROP TRIGGER IF EXISTS trg_usuario_insert $$
CREATE TRIGGER trg_usuario_insert
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, id_usuario, fecha_hora, datos_nuevos)
    VALUES ('usuario', NEW.id_usuario, 'INSERT', NEW.id_usuario, NOW(),
        JSON_OBJECT('username', NEW.username, 'id_rol', NEW.id_rol, 'estado', NEW.estado));
END $$

DROP TRIGGER IF EXISTS trg_usuario_update $$
CREATE TRIGGER trg_usuario_update
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, id_usuario, fecha_hora, datos_viejos, datos_nuevos)
    VALUES ('usuario', NEW.id_usuario, 'UPDATE', NEW.id_usuario, NOW(),
        JSON_OBJECT('username', OLD.username, 'estado', OLD.estado, 'id_rol', OLD.id_rol),
        JSON_OBJECT('username', NEW.username, 'estado', NEW.estado, 'id_rol', NEW.id_rol));
END $$

DROP TRIGGER IF EXISTS trg_usuario_delete $$
CREATE TRIGGER trg_usuario_delete
BEFORE DELETE ON usuario
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, fecha_hora, datos_viejos)
    VALUES ('usuario', OLD.id_usuario, 'DELETE', NOW(),
        JSON_OBJECT('username', OLD.username, 'id_rol', OLD.id_rol));
END $$

-- ============================================================
-- HISTORIALESTADO (cambios de estado de envíos)
-- ============================================================
DROP TRIGGER IF EXISTS trg_historial_insert $$
CREATE TRIGGER trg_historial_insert
AFTER INSERT ON historialestado
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, id_usuario, fecha_hora, datos_nuevos)
    VALUES ('historialestado', NEW.nro_tracking, 'INSERT', NEW.id_usuario, NOW(),
        JSON_OBJECT('nro_tracking', NEW.nro_tracking, 'id_estado', NEW.id_estado, 'observacion', NEW.observacion));
END $$

-- ============================================================
-- VIAJE
-- ============================================================
DROP TRIGGER IF EXISTS trg_viaje_insert $$
CREATE TRIGGER trg_viaje_insert
AFTER INSERT ON viaje
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, fecha_hora, datos_nuevos)
    VALUES ('viaje', NEW.cod_viaje, 'INSERT', NOW(),
        JSON_OBJECT('cod_viaje', NEW.cod_viaje, 'legajo_chofer', NEW.legajo_chofer, 'patente', NEW.patente));
END $$

DROP TRIGGER IF EXISTS trg_viaje_update $$
CREATE TRIGGER trg_viaje_update
AFTER UPDATE ON viaje
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, fecha_hora, datos_viejos, datos_nuevos)
    VALUES ('viaje', NEW.cod_viaje, 'UPDATE', NOW(),
        JSON_OBJECT('legajo_chofer', OLD.legajo_chofer, 'patente', OLD.patente,
                    'fecha_llegada_est', OLD.fecha_llegada_est, 'fecha_llegada_real', OLD.fecha_llegada_real,
                    'cancelado', OLD.cancelado),
        JSON_OBJECT('legajo_chofer', NEW.legajo_chofer, 'patente', NEW.patente,
                    'fecha_llegada_est', NEW.fecha_llegada_est, 'fecha_llegada_real', NEW.fecha_llegada_real,
                    'cancelado', NEW.cancelado));
END $$

-- ============================================================
-- INCIDENTE  (columna correcta: nro_incidente)
-- ============================================================
DROP TRIGGER IF EXISTS trg_incidente_insert $$
CREATE TRIGGER trg_incidente_insert
AFTER INSERT ON incidente
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, fecha_hora, datos_nuevos)
    VALUES ('incidente', NEW.nro_incidente, 'INSERT', NOW(),
        JSON_OBJECT('cod_viaje', NEW.cod_viaje, 'descripcion', NEW.descripcion, 'estado', NEW.estado));
END $$

DROP TRIGGER IF EXISTS trg_incidente_update $$
CREATE TRIGGER trg_incidente_update
AFTER UPDATE ON incidente
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, fecha_hora, datos_viejos, datos_nuevos)
    VALUES ('incidente', NEW.nro_incidente, 'UPDATE', NOW(),
        JSON_OBJECT('estado', OLD.estado, 'descripcion', OLD.descripcion),
        JSON_OBJECT('estado', NEW.estado, 'descripcion', NEW.descripcion));
END $$

DROP TRIGGER IF EXISTS trg_incidente_delete $$
CREATE TRIGGER trg_incidente_delete
BEFORE DELETE ON incidente
FOR EACH ROW
BEGIN
    INSERT INTO auditoria (tabla, id_registro, accion, fecha_hora, datos_viejos)
    VALUES ('incidente', OLD.nro_incidente, 'DELETE', NOW(),
        JSON_OBJECT('cod_viaje', OLD.cod_viaje, 'descripcion', OLD.descripcion, 'estado', OLD.estado));
END $$

DELIMITER ;

-- Verificar que se crearon correctamente
SHOW TRIGGERS FROM logitrack;
