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

## 🔧 Configuración
Crea el archivo `.env.local`:
```
DATABASE_URL=mysql://demo:demo@mariadb:3306/historialclinico
```

## 🌐 Acceso
- **URL local**: http://localhost:18000/login
