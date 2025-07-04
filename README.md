# HelpDeskZ 2.0.2

![HelpDeskZ Logo](/assets/helpdeskz/images/logo.png)

**Versión:** 2.0.2 (9 de marzo de 2021)  
**Desarrollado por:** Andres Mendoza  
[HelpDeskZ - Sitio oficial](https://www.helpdeskz.com)

HelpDeskZ es un software gratuito basado en PHP que permite gestionar el soporte de tu sitio mediante un sistema de tickets accesible desde la web.

---

## 📋 Requisitos del Sistema

- **PHP:** >= 7.3 (recomendado PHP 8.1)
- **Base de datos:** MySQL 5.1+ (recomendado MySQL 8.0)
- **Extensiones PHP requeridas:**
  - `intl` - Internacionalización
  - `fileinfo` - Información de archivos
  - `iconv` - Conversión de caracteres
  - `imap` - Correo electrónico
  - `mbstring` - Cadenas multibyte

---

## 🚀 Instalación Rápida con Docker (Recomendado)

### Requisitos previos
- Docker y Docker Compose instalados

### Servicios incluidos
| Servicio    | Descripción                          | Puerto |
|-------------|--------------------------------------|--------|
| **web**     | Apache + PHP con extensiones        | 8080   |
| **db**      | MySQL 8.0                          | 3306   |
| **phpmyadmin** | Gestión de base de datos         | 8081   |

### Pasos de instalación

1. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   # Edita .env con tus configuraciones
   ```

2. **Construir y ejecutar**
   ```bash
   docker-compose build
   docker-compose up -d
   ```

3. **Instalar dependencias**
   ```bash
   docker-compose exec web composer install
   ```

4. **Acceder a la aplicación**
   - **HelpDesk:** http://localhost:8080
   - **PHPMyAdmin:** http://localhost:8081

### URLs de acceso
- **Cliente:** `http://localhost:8080`
- **Personal/Staff:** `http://localhost:8080/staff`
- **Admin:** `http://localhost:8080/staff` (usar credenciales de admin)

### Funcionalidades destacadas
- **Importación masiva de usuarios:** Sube archivos Excel (.xlsx, .xls) o CSV para crear múltiples usuarios
- **Gestión de tickets y departamentos**
- **Base de conocimientos integrada**
- **API REST para integraciones**

---

## 📦 Dependencias del Proyecto

Este proyecto utiliza **Composer** para gestionar las siguientes dependencias:

- `google/recaptcha` - Sistema de protección reCAPTCHA
- `php-imap/php-imap` - Manejo de correos IMAP  
- `ezyang/htmlpurifier` - Sanitización de contenido HTML
- `zbateson/mail-mime-parser` - Análisis de emails
- `chillerlan/php-qrcode` - Generación de códigos QR
- `phpoffice/phpspreadsheet` - Lectura y escritura de archivos Excel

**Nota:** El directorio `hdz/vendor/` no está incluido en Git y debe instalarse con `composer install`.

---

## ⚙️ Configuración

### Variables de entorno (.env)

```properties
# Base de datos
DB_HOST=db
DB_NAME=helpdesk
DB_USER=helpdesk_user
DB_PASSWORD=tu_password

# Aplicación
SITE_URL=http://localhost:8080/
SITE_NAME=Mi HelpDesk
DEFAULT_LANG=es
STAFF_URI=staff
```

### Credenciales de base de datos
- **Host:** `db` (en Docker) / `localhost` (instalación manual)
- **Base de datos:** `helpdesk`
- **Usuario:** `helpdesk_user`
- **Contraseña:** `helpdesk_password`

---

## 🔧 Comandos Útiles

### Docker
```bash
# Ver logs
docker-compose logs

# Acceder al contenedor
docker-compose exec web bash

# Detener servicios
docker-compose down

# Eliminar datos (¡cuidado!)
docker-compose down -v
```

### Composer
```bash
# Instalar dependencias
docker-compose exec web composer install

# Actualizar dependencias
docker-compose exec web composer update

# Ver dependencias instaladas
docker-compose exec web composer show
```

---

## 🛠️ Instalación Manual (sin Docker)

<details>
<summary>Click para ver instrucciones de instalación manual</summary>

1. **Preparar servidor**
   - Sube los archivos a tu servidor web (ej. `/public_html/support`)
   - Asegúrate de que cumple los requisitos del sistema

2. **Configurar base de datos**
   - Crea una base de datos MySQL
   - Importa el esquema desde `mysql/helpdeskz.sql`

3. **Configurar aplicación**
   - Edita `/hdz/app/Config/Helpdesk.new.php`
   - Renómbralo a `Helpdesk.php`
   - Configura URL del sitio y datos de conexión a BD

4. **Ejecutar instalador**
   - Visita `http://tusitio.com/support/install`
   - Sigue el asistente de instalación
   - **Elimina la carpeta `/hdz/install` al finalizar**

5. **Acceso administrativo**
   - Panel de staff: `http://tusitio.com/support/staff`

</details>

---

## 🆘 Solución de Problemas

### Error de conexión a base de datos
```bash
# Verificar que los contenedores estén activos
docker-compose ps

# Ver logs de la base de datos
docker-compose logs db
```

### Problemas con dependencias
```bash
# Limpiar e instalar
docker-compose exec web composer clear-cache
docker-compose exec web composer install --no-cache
```

### Problemas de permisos
```bash
# Corregir permisos
docker-compose exec web chown -R www-data:www-data /var/www/html
```

---

## 📁 Estructura del Proyecto

```
helpdesk/
├── docker-compose.yml      # Configuración de Docker
├── Dockerfile             # Imagen personalizada de PHP
├── .env                   # Variables de entorno (no versionar)
├── .env.example           # Plantilla de configuración
├── hdz/                   # Aplicación HelpDeskZ
│   ├── app/Config/        # Configuraciones
│   ├── vendor/            # Dependencias (generado por Composer)
│   └── writable/          # Archivos escribibles
├── mysql/                 # Scripts de base de datos
│   └── helpdeskz.sql     # Esquema de la base de datos
└── upload/               # Archivos subidos por usuarios
```

---

## 📄 Licencia

HelpDeskZ es software libre distribuido bajo licencia GPL. Consulta el archivo `LICENSE.txt` para más detalles.

---

## 🤝 Contribuir

Si encuentras errores o quieres contribuir al proyecto:

1. Fork del repositorio
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request
