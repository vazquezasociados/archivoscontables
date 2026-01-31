# ArchivosContables -

_Aplicación para gestión de archivos contables._

## 🚀 Requisitos
- Docker
- PHP 8.2+
- Symfony CLI (opcional)
- Easyadmin 4
- Symfony 6.4

## ⚙️ Instalación
```bash
git clone https://github.com/vazquezasociados/archivoscontables.git
cd archivosContables
docker compose up -d
# Acceder al contenedor PHP (nombre puede variar según tu docker-compose.yml)
docker compose exec php-fpm bash
# Dentro del contenedor:
composer install
yarn install
```

## 🔧 Configuración por unica vez
Crea el archivo `.env.local` y colocar:
```
DATABASE_URL=mysql://demo:demo@mariadb:3306/admincontable
```
Correr migraciones: php bin/console doctrine:migrations:migrate -n -q
```

Correr yarn cada vez que se requiera correr los asset: yarn watch
```
## 🌐 Acceso
- **URL local**: http://localhost:18000/login
