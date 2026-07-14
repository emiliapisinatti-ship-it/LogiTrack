-- ============================================================
-- ÍNDICES — LogiTrack
-- ============================================================
-- Los PKs ya tienen índice automático.
-- Estos índices aceleran las búsquedas más frecuentes del sistema.

-- ENVIO: búsquedas por remitente y destinatario
CREATE INDEX IF NOT EXISTS idx_envio_remitente     ON envio (dni_remitente);
CREATE INDEX IF NOT EXISTS idx_envio_destinatario  ON envio (dni_destinatario);
CREATE INDEX IF NOT EXISTS idx_envio_suc_origen    ON envio (id_suc_origen);
CREATE INDEX IF NOT EXISTS idx_envio_suc_destino   ON envio (id_suc_destino);
CREATE INDEX IF NOT EXISTS idx_envio_fecha         ON envio (fecha_creacion);

-- HISTORIALESTADO: la tabla más consultada (estado actual de cada envío)
CREATE INDEX IF NOT EXISTS idx_historial_tracking  ON historialestado (nro_tracking);
CREATE INDEX IF NOT EXISTS idx_historial_fecha     ON historialestado (fecha_hora);
CREATE INDEX IF NOT EXISTS idx_historial_estado    ON historialestado (id_estado);

-- SUCURSAL: nombre único (evita duplicados)

ALTER TABLE sucursal ADD CONSTRAINT uq_sucursal_nombre UNIQUE (nombre);

-- VIAJE: búsquedas por chofer y vehículo
CREATE INDEX IF NOT EXISTS idx_viaje_chofer        ON viaje (legajo_chofer);
CREATE INDEX IF NOT EXISTS idx_viaje_patente       ON viaje (patente);
CREATE INDEX IF NOT EXISTS idx_viaje_suc_origen    ON viaje (id_suc_origen);

-- INCIDENTE: búsquedas por viaje y estado
CREATE INDEX IF NOT EXISTS idx_incidente_viaje     ON incidente (cod_viaje);
CREATE INDEX IF NOT EXISTS idx_incidente_estado    ON incidente (estado);

-- USUARIO: búsquedas por DNI de cliente y por rol
CREATE INDEX IF NOT EXISTS idx_usuario_dni_cliente ON usuario (dni_cliente);
CREATE INDEX IF NOT EXISTS idx_usuario_rol         ON usuario (id_rol);
CREATE INDEX IF NOT EXISTS idx_usuario_legajo      ON usuario (legajo);

-- EMPLEADO: búsquedas por sucursal y rol
CREATE INDEX IF NOT EXISTS idx_empleado_sucursal   ON empleado (id_sucursal);
CREATE INDEX IF NOT EXISTS idx_empleado_rol        ON empleado (id_rol);

-- AUDITORIA: búsquedas por tabla, acción y fecha
CREATE INDEX IF NOT EXISTS idx_auditoria_tabla     ON auditoria (tabla);
CREATE INDEX IF NOT EXISTS idx_auditoria_accion    ON auditoria (accion);
CREATE INDEX IF NOT EXISTS idx_auditoria_fecha     ON auditoria (fecha_hora);
CREATE INDEX IF NOT EXISTS idx_auditoria_usuario   ON auditoria (id_usuario);

-- Verificar índices creados
SHOW INDEX FROM envio;
SHOW INDEX FROM historialestado;
SHOW INDEX FROM viaje;
