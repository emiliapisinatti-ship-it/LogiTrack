
DELIMITER $$

DROP PROCEDURE IF EXISTS SP_RegistrarNuevoEnvio $$

CREATE PROCEDURE SP_RegistrarNuevoEnvio(
    IN  p_dni_remitente    VARCHAR(20),
    IN  p_dni_destinatario VARCHAR(20),
    IN  p_id_suc_origen    INT,
    IN  p_id_suc_destino   INT,        -- NULL si entrega a domicilio
    IN  p_direccion        VARCHAR(255),-- NULL si retira en sucursal
    IN  p_id_usuario       INT,
    IN  p_peso_kg          DECIMAL(8,2),
    IN  p_alto_cm          INT,
    IN  p_ancho_cm         INT,
    IN  p_largo_cm         INT,
    IN  p_descripcion      TEXT,
    IN  p_id_tipo_cont     INT,
    OUT p_nro_tracking     VARCHAR(20)
)
BEGIN
    DECLARE v_tracking VARCHAR(20);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_nro_tracking = NULL;
    END;

    SET v_tracking = UPPER(SUBSTRING(MD5(UUID()), 1, 10));

    START TRANSACTION;

    INSERT INTO Envio (
        nro_tracking,
        dni_remitente,
        dni_destinatario,
        id_suc_origen,
        id_suc_destino,
        direccion_entrega,
        id_usuario
    ) VALUES (
        v_tracking,
        p_dni_remitente,
        p_dni_destinatario,
        p_id_suc_origen,
        NULLIF(p_id_suc_destino, 0),   -- convierte 0 en NULL
        NULLIF(p_direccion, ''),        -- convierte string vacío en NULL
        p_id_usuario
    );

    INSERT INTO Paquete (
        nro_tracking,
        peso_kg,
        alto_cm,
        ancho_cm,
        largo_cm,
        descripcion,
        id_tipo_cont
    ) VALUES (
        v_tracking,
        p_peso_kg,
        p_alto_cm,
        p_ancho_cm,
        p_largo_cm,
        p_descripcion,
        NULLIF(p_id_tipo_cont, 0)
    );

    COMMIT;

    SET p_nro_tracking = v_tracking;

END $$

DELIMITER ;
