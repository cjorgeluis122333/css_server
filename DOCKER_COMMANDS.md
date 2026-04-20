# 🐳 Laravel en Docker - Guía de Comandos

## ⚡ Cambio clave: Reemplaza `php artisan` por `docker compose exec app php artisan`

### Estado & Logs

```bash
# Ver si los contenedores están corriendo
sudo docker compose ps

# Ver logs en tiempo real (app)
sudo docker compose logs app -f

# Ver logs en tiempo real (mysql)
sudo docker compose logs mysql -f

# Ver últimos 50 logs (app)
sudo docker compose logs app --tail=50
```

---

## 🔧 Comandos Artisan más usados

### **Migraciones**
```bash
# Ejecutar todas las migraciones
sudo docker compose exec app php artisan migrate

# Hacer rollback (deshacer último batch)
sudo docker compose exec app php artisan migrate:rollback

# Reset (borra todo y vuelve a ejecutar)
sudo docker compose exec app php artisan migrate:reset

# Refresh (rollback + migrate)
sudo docker compose exec app php artisan migrate:refresh

# Crear nueva migración
sudo docker compose exec app php artisan make:migration create_tabla_nueva
```

### **Base de datos**
```bash
# Ver estado de migraciones
sudo docker compose exec app php artisan migrate:status

# Seed (llenar BD con datos de prueba)
sudo docker compose exec app php artisan db:seed

# Limpiar BD completamente
sudo docker compose exec app php artisan db:wipe
```

### **Modelos, Controladores, etc.**
```bash
# Crear modelo
sudo docker compose exec app php artisan make:model NombreModelo

# Crear modelo + migration + factory + seeder
sudo docker compose exec app php artisan make:model NombreModelo -a

# Crear controlador
sudo docker compose exec app php artisan make:controller NombreController

# Crear request/validación
sudo docker compose exec app php artisan make:request NombreRequest
```

### **Cache & Config**
```bash
# Limpiar cache de configuración
sudo docker compose exec app php artisan config:clear

# Cachear configuración
sudo docker compose exec app php artisan config:cache

# Limpiar cache de rutas
sudo docker compose exec app php artisan route:clear

# Listar todas las rutas
sudo docker compose exec app php artisan route:list
```

### **Tinker (CLI interactivo)**
```bash
# Entrar a tinker (como en Windows)
sudo docker compose exec app php artisan tinker

# Dentro de tinker, puedes:
# >>> User::all();
# >>> User::create(['name' => 'John', ...]);
# >>> exit
```

---

## 🚀 Levantar / Parar la aplicación

```bash
# Iniciar contenedores (primer uso o después de parar)
sudo docker compose up -d --build

# Parar contenedores (sin borrar datos)
sudo docker compose stop

# Parar y borrar todo (excepto BD si usa volumen)
sudo docker compose down

# Parar, borrar todo Y la BD
sudo docker compose down -v
```

---

## 🌐 Acceder a la aplicación

### **Endpoints de tu API**
```
Base: http://localhost:8080/api/

Ejemplos (según tus rutas):
- POST   http://localhost:8080/api/register     (Registro)
- POST   http://localhost:8080/api/login        (Login)
- GET    http://localhost:8080/api/partners/solvencia
- GET    http://localhost:8080/api/partners/access
- GET    http://localhost:8080/api/user         (requiere token)
```

### **Probar desde terminal**
```bash
# Registro
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"password123"}'

# Login
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# Acceso protegido (necesita token)
curl -H "Authorization: Bearer TOKEN_DEL_LOGIN" \
  http://localhost:8080/api/user
```

---

## 💾 Acceder directamente a MySQL

```bash
# Conectarse a MySQL dentro del contenedor
sudo docker compose exec mysql mysql -uroot -p1 ccv_server

# Comando SQL directo
sudo docker compose exec mysql mysql -uroot -p1 ccv_server -e "SELECT * FROM users;"
```

---

## 📁 Editar archivos en la app

Todos los archivos en el directorio actual se replican en el contenedor automáticamente.  
Edita como normalmente en VS Code y los cambios se aplicarán al instante en la app.

---

## ⚙️ Alias útil (opcional)

Agrega esto a `~/.bashrc` o `~/.zshrc` para no escribir tanto:

```bash
# Alias para docker compose exec app php artisan
alias dart='docker compose exec app php artisan'

# Ahora puedes usar:
# dart migrate
# dart tinker
# dart make:model User
```

Después: `source ~/.bashrc`

---

## 🔄 Workflow típico

1. **Editar código** (archivos locales en VS Code)
2. **Ver logs** en otra terminal: `sudo docker compose logs app -f`
3. **Correr migraciones**: `sudo docker compose exec app php artisan migrate`
4. **Probar API**: `curl http://localhost:8080/api/...`
5. **Debug con tinker**: `sudo docker compose exec app php artisan tinker`

