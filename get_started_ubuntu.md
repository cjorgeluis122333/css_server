# Guía de instalación — CCV Server en Ubuntu 20.04

Guía completa para ejecutar este proyecto Laravel 12 en una computadora con Ubuntu 20.04 desde cero.

> **Probado en:** Ubuntu 20.04 LTS (Focal), HP Pavilion g6, CPU con arquitectura anterior a x86-64-v2.

---

## Requisitos previos del sistema

- Ubuntu 20.04 LTS (Focal)
- Acceso a internet
- Usuario con privilegios `sudo`
- Git instalado (`sudo apt-get install -y git`)

---

## Parte 1 — Instalación del sistema

### 1.1 Instalar Node.js y npm

```bash
# Agregar repositorio de Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -

# Instalar Node.js y npm
sudo apt-get install -y nodejs

# Verificar instalación
node --version
npm --version
```

### 1.2 Instalar Docker Engine

```bash
# Actualizar paquetes e instalar dependencias
sudo apt-get update
sudo apt-get install -y ca-certificates curl gnupg lsb-release

# Agregar clave GPG oficial de Docker
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Agregar repositorio de Docker
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" \
  | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Instalar Docker Engine + Compose Plugin
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Verificar instalación
docker --version
sudo docker compose version
```

### 1.3 Iniciar y habilitar Docker

```bash
sudo systemctl start docker
sudo systemctl enable docker

# Verificar que está activo
sudo systemctl is-active docker
```

> **Nota:** En Ubuntu 20.04, los comandos Docker requieren `sudo`. Si quieres evitar escribir sudo en cada comando, ejecuta: `sudo usermod -aG docker $USER` y luego cierra y abre sesión.

---

## Parte 2 — Obtener el proyecto

### 2.1 Clonar el repositorio

```bash
git clone <URL_DEL_REPOSITORIO> css_server
cd css_server
```

---

## Parte 3 — Configuración del proyecto

### 3.1 Crear el archivo de entorno `.env`

```bash
cp .env.example .env
```

Editar el `.env` con los valores para Docker. Busca estas líneas y modifícalas:

```env
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=ccv_server
DB_USERNAME=root
DB_PASSWORD=1
```

> **Importante:** `DB_HOST=mysql` (no localhost) porque dentro de Docker los servicios se comunican por nombre de servicio.

### 3.2 Instalar dependencias frontend

```bash
# Si la red es lenta, configurar reintentos primero
npm config set fetch-retries 5
npm config set fetch-retry-mintimeout 20000
npm config set fetch-retry-maxtimeout 120000

# Instalar dependencias
npm install

# Compilar assets (CSS y JS con Vite)
npm run build
```

---

## Parte 4 — Levantar la aplicación con Docker

### 4.1 Descargar la imagen de MySQL compatible

> **Crítico para CPUs antiguos:** Las versiones recientes de `mysql:8.0` requieren soporte de instrucciones x86-64-v2 que los CPUs anteriores al 2013 aprox. no tienen. Usa la versión `8.0.28` que es la última compatible.

El archivo `docker-compose.yml` ya tiene configurada la versión correcta (`mysql:8.0.28`). Para pre-descargar la imagen antes de levantar:

```bash
sudo docker compose pull mysql
```

### 4.2 Construir y levantar los contenedores

```bash
sudo docker compose up -d --build
```

Este comando:
1. Construye la imagen de la app (PHP 8.2 + Apache + Composer)
2. Levanta el contenedor de MySQL 8.0.28
3. Levanta el contenedor de la app
4. El script de inicio espera a MySQL y luego ejecuta las migraciones automáticamente

### 4.3 Verificar que todo está corriendo

```bash
sudo docker compose ps
```

Deberías ver algo así:

```
NAME        IMAGE            STATUS          PORTS
ccv_app     css_server-app   Up X minutes    0.0.0.0:8080->80/tcp
ccv_mysql   mysql:8.0.28     Up X minutes    0.0.0.0:3307->3306/tcp
```

### 4.4 Ver logs del arranque

```bash
# Logs de la app (migraciones, errores)
sudo docker compose logs app --tail=50

# Logs de MySQL
sudo docker compose logs mysql --tail=30
```

Los logs de la app deben mostrar al final:
```
Base de datos disponible.
Ejecutando migraciones...
... DONE
Iniciando servidor...
Apache/2.4.x ... configured -- resuming normal operations
```

### 4.5 Verificar que la API responde

```bash
curl -I http://localhost:8080/api/partners/solvencia
# Debe responder: HTTP/1.1 200 OK
```

---

## Parte 5 — Trabajar con el proyecto

### La regla principal

**En Windows:** `php artisan COMANDO`  
**En Ubuntu con Docker:** `sudo docker compose exec app php artisan COMANDO`

### Comandos más usados

```bash
# Ver rutas disponibles
sudo docker compose exec app php artisan route:list

# Ejecutar migraciones
sudo docker compose exec app php artisan migrate

# Deshacer última migración
sudo docker compose exec app php artisan migrate:rollback

# Crear un modelo
sudo docker compose exec app php artisan make:model NombreModelo

# Crear un controlador
sudo docker compose exec app php artisan make:controller NombreController

# Abrir tinker (consola interactiva PHP/Laravel)
sudo docker compose exec app php artisan tinker

# Limpiar cache de configuración
sudo docker compose exec app php artisan config:clear

# Conectarse directamente a MySQL
sudo docker compose exec mysql mysql -uroot -p1 ccv_server
```

### Gestión de contenedores

```bash
# Encender la app (sin reconstruir)
sudo docker compose up -d

# Apagar la app (conserva BD)
sudo docker compose stop

# Apagar y eliminar contenedores (conserva BD en volumen)
sudo docker compose down

# Apagar y eliminar TODO incluida la base de datos
sudo docker compose down -v

# Reconstruir la imagen (cuando cambias el Dockerfile)
sudo docker compose up -d --build
```

### URLs de la API

| Método | URL | Descripción |
|--------|-----|-------------|
| POST | `http://localhost:8080/api/register` | Registro de usuario |
| POST | `http://localhost:8080/api/login` | Login (devuelve token) |
| GET | `http://localhost:8080/api/partners/solvencia` | Solvencia (público) |
| GET | `http://localhost:8080/api/partners/access` | Acceso (público) |
| * | `http://localhost:8080/api/...` | Resto (requiere token Bearer) |

Para rutas protegidas, incluir en el header:
```
Authorization: Bearer TOKEN_OBTENIDO_DEL_LOGIN
```

---

## Parte 6 — Solución de problemas comunes

### Error: `Fatal glibc error: CPU does not support x86-64-v2`

El CPU no soporta instrucciones modernas. Asegúrate de usar `mysql:8.0.28` (no `mysql:8.0` ni `mysql:latest`) en el `docker-compose.yml`:

```yaml
mysql:
  image: mysql:8.0.28   # ← Esta versión específica
```

Si ya tienes la imagen incorrecta descargada:
```bash
sudo docker compose down -v
sudo docker rmi mysql:8.0
sudo docker compose pull mysql
sudo docker compose up -d --build
```

### Error: La app se queda esperando la base de datos indefinidamente

Significa que MySQL tardó más de 2 minutos en inicializarse (normal en la primera ejecución). Reiniciar la app:
```bash
sudo docker compose restart app
```

### Error: `ETIMEDOUT` al ejecutar `npm install`

La red es lenta. Configurar reintentos y volver a intentar:
```bash
npm config set fetch-retries 5
npm config set fetch-retry-mintimeout 20000
npm config set fetch-retry-maxtimeout 120000
npm install
```

### Los cambios en el código no se reflejan

Los archivos locales se copian al contenedor en la construcción. Si cambias código PHP, no necesitas reconstruir (Apache los sirve directamente). Pero si cambias `Dockerfile`, `composer.json` o `package.json`, debes reconstruir:
```bash
sudo docker compose up -d --build
```

Para cambios en archivos JS/CSS, recompilar assets:
```bash
npm run build
```

### Ver todos los logs en tiempo real

```bash
sudo docker compose logs -f
```

---

## Resumen de comandos de arranque rápido

Para levantar el proyecto después de la instalación inicial:

```bash
cd css_server
sudo docker compose up -d
```

Para la primera vez (instalación completa):

```bash
cd css_server
cp .env.example .env
# Editar .env con los valores de la Parte 3.1
npm install && npm run build
sudo docker compose up -d --build
```
