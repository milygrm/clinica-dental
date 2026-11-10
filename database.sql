
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  clave VARCHAR(255) NOT NULL,
  creado DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pacientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cedula VARCHAR(20) NOT NULL UNIQUE,
  nombres VARCHAR(80) NOT NULL,
  apellidos VARCHAR(80) NOT NULL,
  fecha_nacimiento DATE NOT NULL,
  sexo ENUM('M','F','O') NOT NULL DEFAULT 'M',
  telefono VARCHAR(30) DEFAULT NULL,
  email VARCHAR(120) DEFAULT NULL,
  direccion VARCHAR(255) DEFAULT NULL,
  alergias TEXT DEFAULT NULL,
  antecedentes TEXT DEFAULT NULL,
  creado DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS procedimientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT DEFAULT NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS atenciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  paciente_id INT NOT NULL,
  procedimiento_id INT NOT NULL,
  fecha DATE NOT NULL,
  pieza_dental VARCHAR(20) DEFAULT NULL,
  diagnostico TEXT DEFAULT NULL,
  observaciones TEXT DEFAULT NULL,
  costo DECIMAL(10,2) NOT NULL DEFAULT 0,
  creado DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_at_pac FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
  CONSTRAINT fk_at_proc FOREIGN KEY (procedimiento_id) REFERENCES procedimientos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario inicial: admin / admin123  (cámbielo después de entrar)
INSERT INTO usuarios (nombre, usuario, clave) VALUES
('Dr. Administrador', 'admin', '$2y$10$QDEcuKQKIzklOfQqB1tb2O4z6Lk67tld8nxqzS/JIDGhPAx9m8kxa');

INSERT INTO procedimientos (nombre, descripcion, precio) VALUES
('Limpieza dental', 'Profilaxis y remoción de sarro y placa bacteriana.', 30.00),
('Blanqueamiento dental', 'Aclaramiento del color de los dientes con agente blanqueador.', 120.00),
('Extracción de muela', 'Extracción simple de pieza dental.', 40.00),
('Extracción de cordal', 'Extracción quirúrgica de tercer molar (muela del juicio).', 90.00),
('Tratamiento de conducto', 'Endodoncia: limpieza y obturación del conducto radicular.', 150.00),
('Calza (obturación)', 'Restauración de pieza con caries mediante resina compuesta.', 35.00);
