
DELIMITER $$

DROP PROCEDURE IF EXISTS SP_CambiarEstadoEnvio $$

CREATE PROCEDURE SP_CambiarEstadoEnvio(
    IN  p_nro_tracking  VARCHAR(20)  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    IN  p_id_estado     INT,
    IN  p_id_usuario    INT,
    IN  p_observacion   TEXT         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    OUT p_resultado     VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
)
BEGIN
    DECLARE v_existe_envio  INT DEFAULT 0;
    DECLARE v_existe_estado INT DEFAULT 0;
    DECLARE v_id_sucursal   INT DEFAULT NULL;
    DECLARE v_err_msg       TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '';

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 v_err_msg = MESSAGE_TEXT;
        ROLLBACK;
        SET p_resultado = CONCAT('ERROR: ', v_err_msg);
    END;

    SELECT COUNT(*) INTO v_existe_envio
    FROM Envio WHERE nro_tracking = p_nro_tracking;

    IF v_existe_envio = 0 THEN
        SET p_resultado = 'ERROR: el número de tracking no existe';
    ELSE
        SELECT COUNT(*) INTO v_existe_estado
        FROM EstadoEnvio WHERE id_estado = p_id_estado;

        IF v_existe_estado = 0 THEN
            SET p_resultado = 'ERROR: estado inválido';
        ELSE
            SELECT e.id_sucursal INTO v_id_sucursal
            FROM empleado e
            JOIN usuario u ON u.legajo = e.legajo
            WHERE u.id_usuario = p_id_usuario
            LIMIT 1;

            IF v_id_sucursal IS NULL THEN
                -- Para choferes: usar sucursal destino del envío (o fallback a origen para domicilios)
                SELECT COALESCE(id_suc_destino, id_suc_origen) INTO v_id_sucursal
                FROM Envio WHERE nro_tracking = p_nro_tracking;
            END IF;

            START TRANSACTION;

            INSERT INTO HistorialEstado (
                nro_tracking, id_estado, id_sucursal,
                id_usuario, observacion, fecha_hora
            ) VALUES (
                p_nro_tracking, p_id_estado, v_id_sucursal,
                p_id_usuario, NULLIF(p_observacion, ''), NOW()
            );

            COMMIT;
            SET p_resultado = 'OK';
        END IF;
    END IF;

END $$

DELIMITER ;
