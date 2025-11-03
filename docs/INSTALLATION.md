# Net Inventory - Installation Guide

## Guía de Instalación Completa

---

## 📋 Requisitos Previos

### Requisitos del Sistema

- **PHP:** 7.4 o superior
- **MySQL:** 5.7+ o MariaDB 10.3+
- **Servidor Web:** Apache 2.4+, Nginx 1.18+, o PHP Built-in Server
- **Sistema Operativo:** Windows, Linux, o macOS

### Extensiones PHP Requeridas

```ini
extension=pdo_mysql
extension=mbstring
extension=json
extension=openssl
```

Verificar extensiones:
```bash
php -m | grep -E "pdo_mysql|mbstring|json"
```

---

## 🚀 Instalación en Windows (XAMPP)

### Paso 1: Instalar XAMPP

1. Descargar XAMPP desde: https://www.apachefriends.org/
2. Instalar con PHP 7.4 o superior
3. Iniciar Apache y MySQL desde el panel de control

### Paso 2: Configurar el Proyecto

```cmd
:: Copiar proyecto a htdocs
xcopy "C:\Users\fsant\Downloads\Net Inventory" "C:\xampp\htdocs\net-inventory\" /E /I

:: Navegar al directorio
cd C:\xampp\htdocs\net-inventory
```

### Paso 3: Crear Base de Datos

```cmd
:: Abrir MySQL desde línea de comandos
C:\xampp\mysql\bin\mysql.exe -u root -p

:: O usar phpMyAdmin
:: URL: http://localhost/phpmyadmin
```

En MySQL:
```sql
SOURCE C:/xampp/htdocs/net-inventory/migrations/001_init.sql;
SOURCE C:/xampp/htdocs/net-inventory/migrations/002_seed_data.sql;
```

### Paso 4: Configurar Variables de Entorno

Copiar `.env.example` a `.env`:
```cmd
copy .env.example .env
```

Editar `.env`:
```env
DB_HOST=localhost
DB_NAME=net_inventory
DB_USER=root
DB_PASS=
BASE_URL=http://localhost/net-inventory/public
```

### Paso 5: Configurar Apache (Opcional)

Editar `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName netinventory.local
    DocumentRoot "C:/xampp/htdocs/net-inventory/public"
    
    <Directory "C:/xampp/htdocs/net-inventory/public">
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>
</VirtualHost>
```

Agregar a `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 netinventory.local
```

Reiniciar Apache y acceder a: http://netinventory.local

### Paso 6: Acceder al Sistema

```
URL: http://localhost/net-inventory/public
Usuario: admin
Contraseña: password123
```

---

## 🐧 Instalación en Linux (Ubuntu/Debian)

### Paso 1: Instalar Dependencias

```bash
# Actualizar repositorios
sudo apt update

# Instalar Apache, PHP y MySQL
sudo apt install apache2 php php-mysql php-mbstring php-json mysql-server

# Verificar instalación
php -v
mysql --version
```

### Paso 2: Configurar MySQL

```bash
# Iniciar MySQL
sudo systemctl start mysql
sudo systemctl enable mysql

# Configurar MySQL (opcional)
sudo mysql_secure_installation

# Acceder a MySQL
sudo mysql -u root -p
```

Crear base de datos:
```sql
SOURCE /path/to/net-inventory/migrations/001_init.sql;
SOURCE /path/to/net-inventory/migrations/002_seed_data.sql;
exit;
```

### Paso 3: Copiar Proyecto

```bash
# Copiar a directorio web
sudo cp -r "/path/to/Net Inventory" /var/www/html/net-inventory

# Ajustar permisos
sudo chown -R www-data:www-data /var/www/html/net-inventory
sudo chmod -R 755 /var/www/html/net-inventory
```

### Paso 4: Configurar Variables

```bash
cd /var/www/html/net-inventory
cp .env.example .env
nano .env
```

Configurar:
```env
DB_HOST=localhost
DB_NAME=net_inventory
DB_USER=root
DB_PASS=your_mysql_password
BASE_URL=http://localhost/net-inventory/public
```

### Paso 5: Configurar Apache

Crear VirtualHost:
```bash
sudo nano /etc/apache2/sites-available/netinventory.conf
```

Contenido:
```apache
<VirtualHost *:80>
    ServerName netinventory.local
    DocumentRoot /var/www/html/net-inventory/public
    
    <Directory /var/www/html/net-inventory/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/netinventory_error.log
    CustomLog ${APACHE_LOG_DIR}/netinventory_access.log combined
</VirtualHost>
```

Habilitar sitio:
```bash
sudo a2ensite netinventory.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Agregar al `/etc/hosts`:
```bash
sudo nano /etc/hosts
# Agregar:
127.0.0.1 netinventory.local
```

### Paso 6: Acceder

```
URL: http://netinventory.local
Usuario: admin
Contraseña: password123
```

---

## 🐳 Instalación con Docker (Recomendado para Testing)

### Dockerfile

Crear `Dockerfile` en la raíz del proyecto:

```dockerfile
FROM php:7.4-apache

# Instalar extensiones
RUN docker-php-ext-install pdo_mysql mysqli

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar proyecto
COPY . /var/www/html/

# Configurar DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permisos
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80
```

### docker-compose.yml

```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=net_inventory
      - DB_USER=netinv
      - DB_PASS=vulnerable123
  
  db:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      MYSQL_ROOT_PASSWORD: rootpass
      MYSQL_DATABASE: net_inventory
      MYSQL_USER: netinv
      MYSQL_PASSWORD: vulnerable123
    volumes:
      - ./migrations:/docker-entrypoint-initdb.d
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

### Iniciar con Docker

```bash
# Construir e iniciar
docker-compose up -d

# Ver logs
docker-compose logs -f

# Acceder
# URL: http://localhost:8000
```

---

## 🧪 Usando PHP Built-in Server (Desarrollo Rápido)

```bash
cd "C:\Users\fsant\Downloads\Net Inventory\public"
php -S localhost:8000

# Acceder a: http://localhost:8000
```

---

## 📦 Importar Datos de Ejemplo

```bash
# Importar dispositivos desde CSV
php scripts/import_devices.php scripts/sample_devices.csv
```

---

## 🔧 Solución de Problemas

### Error: "Connection refused"

```bash
# Verificar que MySQL esté corriendo
# Windows (XAMPP)
netstat -an | findstr :3306

# Linux
sudo systemctl status mysql
```

### Error: "Access denied for user"

Verificar credenciales en `.env` y permisos de usuario MySQL:
```sql
GRANT ALL PRIVILEGES ON net_inventory.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Error: "Call to undefined function mysqli_connect()"

Habilitar extensión en `php.ini`:
```ini
extension=mysqli
extension=pdo_mysql
```

Reiniciar servidor web.

### Error 404 en rutas

Verificar que mod_rewrite esté habilitado (Apache):
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## ✅ Verificación Post-Instalación

### Checklist

- [ ] Página de login carga correctamente
- [ ] Login exitoso con `admin / password123`
- [ ] Dashboard muestra estadísticas
- [ ] Lista de dispositivos carga con datos de ejemplo
- [ ] Puede crear un nuevo dispositivo
- [ ] Puede ver detalles de un dispositivo
- [ ] Puede subir una configuración
- [ ] Export a CSV funciona

### Test de SQL Injection

Verificar que las vulnerabilidades estén activas:

```bash
# Test básico en login
curl -X POST http://localhost:8000/login \
  -d "username=admin'--&password=x" \
  -L

# Si redirige al dashboard, la vulnerabilidad está activa
```

---

## 📞 Soporte

Para problemas de instalación, revisar:
- README.md principal
- docs/SQL_INJECTION_GUIDE.md

---

**Versión:** 1.0  
**Última actualización:** Noviembre 2025
