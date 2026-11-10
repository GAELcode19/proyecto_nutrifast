-- Script de Creación de Base de Datos NutriFast
-- Motor: MySQL 8.0.16 o superior
-- Codificación: utf8mb4

CREATE DATABASE IF NOT EXISTS nutrifast CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nutrifast;

-- 1. Tabla de Usuarios (Nutrióloga)
CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

-- 2. Tabla de Pacientes (F1)
CREATE TABLE pacientes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo ENUM('F', 'M') NOT NULL,
    estatura_m DECIMAL(3,2) NOT NULL,
    telefono VARCHAR(20) NULL,
    correo VARCHAR(150) NULL,
    antecedentes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_pacientes_nombre (apellidos, nombre),
    CONSTRAINT chk_pacientes_estatura CHECK (estatura_m BETWEEN 0.50 AND 2.50),
    CONSTRAINT chk_pacientes_nombre CHECK (CHAR_LENGTH(TRIM(nombre)) > 0),
    CONSTRAINT chk_pacientes_apellidos CHECK (CHAR_LENGTH(TRIM(apellidos)) > 0)
) ENGINE=InnoDB;

-- 3. Tabla de Consultas (F2)
CREATE TABLE consultas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    paciente_id BIGINT UNSIGNED NOT NULL,
    fecha_consulta DATETIME NOT NULL,
    observaciones TEXT NULL,
    plan_alimentacion TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_consultas_paciente_fecha (paciente_id, fecha_consulta),
    CONSTRAINT fk_consultas_paciente FOREIGN KEY (paciente_id) REFERENCES pacientes(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 4. Tabla de Mediciones (F2 y F3)
CREATE TABLE mediciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    consulta_id BIGINT UNSIGNED NOT NULL UNIQUE,
    peso DECIMAL(5,2) NOT NULL,
    imc DECIMAL(4,2) NOT NULL,
    porcentaje_grasa DECIMAL(4,1) NULL,
    agua_corporal DECIMAL(4,1) NULL,
    masa_muscular DECIMAL(5,2) NULL,
    masa_osea DECIMAL(4,2) NULL,
    grasa_visceral TINYINT UNSIGNED NULL,
    tasa_metabolica_basal SMALLINT UNSIGNED NULL,
    edad_metabolica TINYINT UNSIGNED NULL,
    valoracion_fisica TINYINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_mediciones_consulta FOREIGN KEY (consulta_id) REFERENCES consultas(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_med_peso CHECK (peso > 0 AND peso <= 400),
    CONSTRAINT chk_med_imc CHECK (imc > 0 AND imc <= 90),
    CONSTRAINT chk_med_grasa CHECK (porcentaje_grasa IS NULL OR porcentaje_grasa BETWEEN 0 AND 75),
    CONSTRAINT chk_med_agua CHECK (agua_corporal IS NULL OR agua_corporal BETWEEN 0 AND 100),
    CONSTRAINT chk_med_muscular CHECK (masa_muscular IS NULL OR masa_muscular > 0),
    CONSTRAINT chk_med_osea CHECK (masa_osea IS NULL OR masa_osea > 0),
    CONSTRAINT chk_med_visceral CHECK (grasa_visceral IS NULL OR grasa_visceral BETWEEN 1 AND 59),
    CONSTRAINT chk_med_tmb CHECK (tasa_metabolica_basal IS NULL OR tasa_metabolica_basal BETWEEN 500 AND 6000),
    CONSTRAINT chk_med_edad_met CHECK (edad_metabolica IS NULL OR edad_metabolica BETWEEN 5 AND 120),
    CONSTRAINT chk_med_valoracion CHECK (valoracion_fisica IS NULL OR valoracion_fisica BETWEEN 1 AND 9)
) ENGINE=InnoDB;

-- 5. Tabla de Medicamentos Registrados (Separada del texto libre)
CREATE TABLE medicamentos_registrados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    consulta_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    dosis VARCHAR(80) NULL,
    frecuencia VARCHAR(80) NULL,
    indicaciones VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_medicamentos_consulta FOREIGN KEY (consulta_id) REFERENCES consultas(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_medicamento_nombre CHECK (CHAR_LENGTH(TRIM(nombre)) > 0)
) ENGINE=InnoDB;

-- 6. Tabla de Citas (F4)
CREATE TABLE citas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    paciente_id BIGINT UNSIGNED NOT NULL,
    fecha_hora DATETIME NOT NULL,
    motivo VARCHAR(150) NULL,
    estado ENUM('programada', 'atendida', 'cancelada') DEFAULT 'programada',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_citas_fecha (fecha_hora),
    INDEX idx_citas_paciente (paciente_id, fecha_hora),
    CONSTRAINT fk_citas_paciente FOREIGN KEY (paciente_id) REFERENCES pacientes(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;