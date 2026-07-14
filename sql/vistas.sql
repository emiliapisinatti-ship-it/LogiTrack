-- ============================================================
-- VISTAS (SQL VIEWs) — LogiTrack
-- ============================================================

-- Vista: envíos con toda la información relevante
-- Evita repetir el JOIN complejo en cada consulta del sistema
CREATE OR REPLACE VIEW vista_envios_completos AS
SELECT
    e.nro_tracking,
    e.fecha_creacion,
    e.direccion_entrega,
    e.id_suc_origen,
    e.id_suc_destino,

    -- Remitente
    cr.dni        AS dni_remitente,
    cr.nombre     AS nombre_remitente,
    cr.apellido   AS apellido_remitente,

    -- Destinatario
    cd.dni        AS dni_destinatario,
    cd.nombre     AS nombre_dest,
    cd.apellido   AS apellido_dest,

    -- Sucursales
    so.nombre     AS suc_origen,
    sd.nombre     AS suc_destino,

    -- Estado actual (último registro de historial)
    h.id_estado   AS id_estado_hist,
    es.nombre     AS estado_actual,
    h.fecha_hora  AS fecha_ultimo_estado

FROM envio e
LEFT JOIN cliente       cr ON cr.dni        = e.dni_remitente
LEFT JOIN cliente       cd ON cd.dni        = e.dni_destinatario
LEFT JOIN sucursal      so ON so.id_sucursal = e.id_suc_origen
LEFT JOIN sucursal      sd ON sd.id_sucursal = e.id_suc_destino
LEFT JOIN historialestado h ON h.id_hist = (
        SELECT id_hist FROM historialestado h2
        WHERE h2.nro_tracking = e.nro_tracking
        ORDER BY h2.fecha_hora DESC, h2.id_hist DESC
        LIMIT 1
    )
LEFT JOIN estadoenvio   es ON es.id_estado  = h.id_estado;


-- Vista: todos los viajes con datos completos (usada por el listado admin)
CREATE OR REPLACE VIEW vista_viajes_completos AS
SELECT
    v.cod_viaje,
    v.fecha_salida,
    v.fecha_llegada_est,
    v.fecha_llegada_real,
    v.patente,
    v.legajo_chofer,
    v.cancelado,
    v.id_suc_origen,
    e.nombre     AS nombre_chofer,
    e.apellido   AS apellido_chofer,
    s.nombre     AS suc_origen,
    tv.nombre    AS tipo_vehiculo,
    COUNT(ve.nro_tracking) AS total_envios
FROM viaje v
LEFT JOIN empleado    e  ON e.legajo       = v.legajo_chofer
LEFT JOIN sucursal    s  ON s.id_sucursal  = v.id_suc_origen
LEFT JOIN vehiculo    vh ON vh.patente     = v.patente
LEFT JOIN tipovehiculo tv ON tv.id_tipo_veh = vh.id_tipo_veh
LEFT JOIN viaje_envio ve ON ve.cod_viaje   = v.cod_viaje
GROUP BY
    v.cod_viaje, v.fecha_salida, v.fecha_llegada_est, v.fecha_llegada_real,
    v.patente, v.legajo_chofer, v.cancelado, v.id_suc_origen,
    e.nombre, e.apellido, s.nombre, tv.nombre;


-- Vista: viajes activos (sin fecha de llegada real y no cancelados)
-- Útil para el panel del admin y para asignar envíos
CREATE OR REPLACE VIEW vista_viajes_activos AS
SELECT
    v.cod_viaje,
    v.fecha_salida,
    v.fecha_llegada_est,
    v.patente,
    v.legajo_chofer,
    e.nombre     AS nombre_chofer,
    e.apellido   AS apellido_chofer,
    s.nombre     AS suc_origen,
    tv.nombre    AS tipo_vehiculo,
    COUNT(ve.nro_tracking) AS total_envios
FROM viaje v
LEFT JOIN empleado    e  ON e.legajo       = v.legajo_chofer
LEFT JOIN sucursal    s  ON s.id_sucursal  = v.id_suc_origen
LEFT JOIN vehiculo    vh ON vh.patente     = v.patente
LEFT JOIN tipovehiculo tv ON tv.id_tipo_veh = vh.id_tipo_veh
LEFT JOIN viaje_envio ve ON ve.cod_viaje   = v.cod_viaje
WHERE v.fecha_llegada_real IS NULL
  AND v.cancelado = 0
GROUP BY
    v.cod_viaje, v.fecha_salida, v.fecha_llegada_est, v.patente,
    v.legajo_chofer, e.nombre, e.apellido, s.nombre, tv.nombre;


-- Vista: incidentes abiertos con detalle
-- Permite al admin ver rápidamente qué incidentes requieren atención
CREATE OR REPLACE VIEW vista_incidentes_abiertos AS
SELECT
    i.nro_incidente,
    i.fecha_hora,
    i.descripcion,
    i.cod_viaje,
    t.nombre      AS tipo,
    v.patente,
    e.nombre      AS nombre_chofer,
    e.apellido    AS apellido_chofer
FROM incidente i
LEFT JOIN tipoincidente t  ON t.id_tipo_inc  = i.id_tipo_inc
LEFT JOIN viaje         v  ON v.cod_viaje    = i.cod_viaje
LEFT JOIN empleado      e  ON e.legajo       = v.legajo_chofer
WHERE i.estado = 'abierto'
ORDER BY i.fecha_hora DESC;


-- Vista: resumen de envíos por sucursal y estado
-- Usada en el panel de reportes para estadísticas rápidas
CREATE OR REPLACE VIEW vista_resumen_por_sucursal AS
SELECT
    s.id_sucursal,
    s.nombre      AS sucursal,
    es.nombre     AS estado,
    COUNT(*)      AS cantidad
FROM envio e
JOIN sucursal      s  ON s.id_sucursal  = e.id_suc_origen
LEFT JOIN historialestado h ON h.id_hist = (
        SELECT id_hist FROM historialestado h2
        WHERE h2.nro_tracking = e.nro_tracking
        ORDER BY h2.fecha_hora DESC, h2.id_hist DESC
        LIMIT 1
    )
LEFT JOIN estadoenvio es ON es.id_estado = h.id_estado
GROUP BY s.id_sucursal, s.nombre, es.nombre
ORDER BY s.nombre, es.nombre;
