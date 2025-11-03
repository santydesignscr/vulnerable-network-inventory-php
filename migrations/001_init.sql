-- ============================================================================
-- Net Inventory Database Schema
-- MySQL/MariaDB - InnoDB, utf8mb4
-- ⚠️ FOR EDUCATIONAL PURPOSES - Contains deliberate security weaknesses
-- ============================================================================

-- CREATE DATABASE
CREATE DATABASE IF NOT EXISTS net_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE net_inventory;

-- ============================================================================
-- Tabla de usuarios (roles mínimos)
-- ============================================================================
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(150) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('viewer','operator','admin') NOT NULL DEFAULT 'viewer',
  full_name VARCHAR(150) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL,
  is_active TINYINT(1) DEFAULT 1,
  INDEX idx_username (username),
  INDEX idx_email (email)
) ENGINE=InnoDB;

-- ============================================================================
-- Tabla de tipos de dispositivo (router, switch, firewall, ap)
-- ============================================================================
CREATE TABLE device_types (
  id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(30) NOT NULL UNIQUE,
  label VARCHAR(60) NOT NULL
) ENGINE=InnoDB;

-- ============================================================================
-- Tabla de fabricantes/modelos
-- ============================================================================
CREATE TABLE vendors (
  id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE device_models (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vendor_id SMALLINT UNSIGNED NOT NULL,
  model_name VARCHAR(120) NOT NULL,
  os_family VARCHAR(60) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  UNIQUE (vendor_id, model_name),
  FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================================
-- Tabla de ubicaciones (sede / rack / sala)
-- ============================================================================
CREATE TABLE locations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  parent_id INT UNSIGNED DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  notes TEXT,
  FOREIGN KEY (parent_id) REFERENCES locations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================================
-- Tabla principal: dispositivos
-- ============================================================================
CREATE TABLE devices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  hostname VARCHAR(100) NOT NULL,
  management_ip VARBINARY(16) DEFAULT NULL, -- IPv4/IPv6 store using INET6_ATON/INET6_NTOA
  ipv4 VARCHAR(45) GENERATED ALWAYS AS (INET6_NTOA(management_ip)) VIRTUAL,
  device_type_id SMALLINT UNSIGNED NOT NULL,
  model_id INT UNSIGNED DEFAULT NULL,
  serial_number VARCHAR(120) DEFAULT NULL,
  ios_version VARCHAR(100) DEFAULT NULL,
  location_id INT UNSIGNED DEFAULT NULL,
  owner VARCHAR(100) DEFAULT NULL,
  purchase_date DATE DEFAULT NULL,
  warranty_until DATE DEFAULT NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY ux_hostname (hostname),
  INDEX idx_mgmt_ip (management_ip),
  INDEX idx_device_type (device_type_id),
  INDEX idx_location (location_id),
  FOREIGN KEY (device_type_id) REFERENCES device_types(id) ON DELETE RESTRICT,
  FOREIGN KEY (model_id) REFERENCES device_models(id) ON DELETE SET NULL,
  FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================================
-- Tabla para configuraciones (texto plano o archivos)
-- ============================================================================
CREATE TABLE configs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  device_id INT UNSIGNED NOT NULL,
  filename VARCHAR(200) DEFAULT NULL,
  uploaded_by INT UNSIGNED DEFAULT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  content MEDIUMTEXT,
  storage_path VARCHAR(300) DEFAULT NULL, -- si guardas en filesystem
  notes TEXT,
  FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_device_uploaded_at (device_id, uploaded_at)
) ENGINE=InnoDB;

-- ============================================================================
-- Tabla de puertos/interfaces (opcional para inventario detallado)
-- ============================================================================
CREATE TABLE interfaces (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  device_id INT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL, -- e.g. GigabitEthernet0/1
  description VARCHAR(150) DEFAULT NULL,
  speed VARCHAR(30) DEFAULT NULL,
  mac_address VARBINARY(6) DEFAULT NULL,
  admin_status ENUM('up','down','unknown') DEFAULT 'unknown',
  oper_status ENUM('up','down','unknown','dormant') DEFAULT 'unknown',
  ip_address VARBINARY(16) DEFAULT NULL,
  notes TEXT,
  UNIQUE KEY ux_device_iface (device_id, name),
  FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- Registro de cambios / auditoría
-- ============================================================================
CREATE TABLE change_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  device_id INT UNSIGNED DEFAULT NULL,
  user_id INT UNSIGNED DEFAULT NULL,
  action VARCHAR(60) NOT NULL, -- create/update/upload-config/delete
  details JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_action_time (action, created_at),
  INDEX idx_device_id (device_id)
) ENGINE=InnoDB;

-- ============================================================================
-- Tabla de asignaciones IP (IPAM ligera)
-- ============================================================================
CREATE TABLE ip_assignments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  device_id INT UNSIGNED DEFAULT NULL,
  interface_id INT UNSIGNED DEFAULT NULL,
  ip VARBINARY(16) NOT NULL,
  prefix TINYINT UNSIGNED DEFAULT NULL,
  assigned_for VARCHAR(120) DEFAULT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY ux_ip (ip, prefix),
  FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE SET NULL,
  FOREIGN KEY (interface_id) REFERENCES interfaces(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================================
-- Nota sobre IPs:
-- - management_ip e ip en ip_assignments se insertan usando INET6_ATON('ip')
-- - Se muestran con INET6_NTOA(field)
-- - Soporta IPv4 e IPv6 en el mismo campo
-- ============================================================================
