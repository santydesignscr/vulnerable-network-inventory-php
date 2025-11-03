# Quick Start Guide - Net Inventory

## 🚀 Inicio Rápido (5 minutos)

### Para Windows con XAMPP

1. **Iniciar servicios**
   - Abrir XAMPP Control Panel
   - Iniciar Apache y MySQL

2. **Crear base de datos**
   ```cmd
   cd C:\xampp\mysql\bin
   mysql.exe -u root -p
   ```
   
   En MySQL:
   ```sql
   CREATE DATABASE net_inventory;
   USE net_inventory;
   SOURCE /ruta/completa/al/proyecto/migrations/001_init.sql;
   SOURCE /ruta/completa/al/proyecto/migrations/002_seed_data.sql;
   exit;
   ```
   
   **Nota:** Reemplaza `/ruta/completa/al/proyecto/` con la ruta real donde descargaste el proyecto.

3. **Configurar proyecto**
   ```cmd
   cd "ruta\completa\al\proyecto"
   copy .env.example .env
   ```
   
   No es necesario editar `.env` si usas configuración por defecto de XAMPP.

4. **Iniciar servidor**
   ```cmd
   cd public
   php -S localhost:8000
   ```

5. **Acceder**
   - URL: http://localhost:8000
   - Usuario: `admin`
   - Contraseña: `password123`

---

## 🧪 Prueba Rápida de Vulnerabilidad

### Test 1: SQL Injection en Login

1. Ir a: http://localhost:8000/login
2. En "Username" ingresar: `admin'--`
3. En "Password" cualquier cosa o dejar vacío
4. Click en Login
5. ✅ Si accedes al dashboard, la vulnerabilidad está activa

### Test 2: SQL Injection en Búsqueda

1. Ir a: http://localhost:8000/devices
2. En el campo de búsqueda ingresar:
   ```
   %' UNION SELECT 1,username,password_hash,4,5,6,7,8,9,10,11,12,13,14,15 FROM users--
   ```
3. ✅ Deberías ver usuarios y hashes en la tabla

### Test 3: Custom Query (Solo Admin)

1. Acceder como admin al Dashboard
2. Scroll down hasta "Custom SQL Query Tool"
3. Ejecutar:
   ```sql
   SELECT * FROM users
   ```
4. ✅ Deberías ver todos los usuarios

---

## 📚 Credenciales de Prueba

```
Admin:
  Usuario: admin
  Password: password123
  Rol: admin (acceso completo)

Operador:
  Usuario: operator1
  Password: password123
  Rol: operator (crear/editar dispositivos)

Visualizador:
  Usuario: viewer1
  Password: password123
  Rol: viewer (solo lectura)
```

---

## 🎯 Objetivos de Aprendizaje

1. ✅ Identificar vulnerabilidades SQL Injection
2. ✅ Explotar diferentes tipos de inyección
3. ✅ Documentar hallazgos
4. ✅ Comprender el impacto
5. ✅ Aprender técnicas de remediación

---

## 📖 Documentación Completa

- `README.md` - Información general y características
- `docs/INSTALLATION.md` - Guía de instalación detallada
- `docs/SQL_INJECTION_GUIDE.md` - Payloads y técnicas de explotación
- `SECURITY.md` - Política de seguridad

---

## 🛠️ Comandos Útiles

### Importar dispositivos de ejemplo
```cmd
php scripts/import_devices.php scripts/sample_devices.csv
```

### Reiniciar base de datos
```sql
DROP DATABASE net_inventory;
CREATE DATABASE net_inventory;
USE net_inventory;
SOURCE migrations/001_init.sql;
SOURCE migrations/002_seed_data.sql;
```

### Ver logs de errores (PHP)
```cmd
# En public/
tail -f error.log
```

---

## ⚠️ Recordatorio Importante

Este sistema es **INTENCIONALMENTE VULNERABLE**. 

✅ Usar SOLO para:
- Aprendizaje
- Pruebas de penetración autorizadas
- Entornos de laboratorio

❌ NUNCA usar para:
- Producción
- Datos reales
- Sistemas accesibles por Internet

---

## 🆘 Solución de Problemas Rápidos

### "Connection refused"
→ Verificar que MySQL esté corriendo en XAMPP

### "Access denied"
→ Verificar usuario/contraseña en `.env`

### "Table doesn't exist"
→ Ejecutar los scripts SQL de migración

### "404 Not Found"
→ Verificar que estás en el directorio `/public` al iniciar PHP server

---

## 📞 Siguiente Paso

Una vez que hayas verificado que todo funciona:

1. Lee `docs/SQL_INJECTION_GUIDE.md` para exploits detallados
2. Practica los payloads documentados
3. Usa SQLMap para pruebas automatizadas
4. Documenta tus hallazgos

---

**¡Buena suerte con tus pruebas de seguridad!** 🔐

---

**Versión:** 1.0  
**Última actualización:** Noviembre 2025
