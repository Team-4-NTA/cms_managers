# Hướng Dẫn Cài Đặt Laravel CMS (Laravel 11 + Nuxt 3) Trên EC2 ubuntu

## 1. Tạo EC2 Instance
- **Loại máy**: t3.medium (20GB ổ đĩa)
    - *Lưu ý:* Bản miễn phí t2.micro không đủ tài nguyên.
- **Hệ điều hành**: Ubuntu 22.04 hoặc 20.04.
- **Mở cổng trong Security Group:**
    - `80, 443`: Cho Nginx reverse proxy.
    - `3000`: Dùng cho Nuxt.
    - `22`: SSH.

---

## 2. Cài Đặt Docker & Docker Compose
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y docker.io docker-compose
sudo systemctl enable docker
sudo usermod -aG docker $USER  # Thêm quyền cho user hiện tại
```
*Đăng xuất và đăng nhập lại để áp dụng quyền (thoát SSH EC2 và đăng nhập lại).*  
Kiểm tra Docker:
```bash
docker ps
```

---

## 3. Clone Source Code
```bash
git clone https://github.com/nguyennhnta/cms_manager.git
cd cms_manager
```
- **Laravel API**: `laravel_cms`
- **Nuxt Frontend**: `nuxt_cms`

### Cài đặt Laravel API
#### Cấu hình Nginx (`docker-compose/nginx/laravel_cms.conf`)
```nginx
server {
    listen 80; #port của container docker
    server_name localhost;
    root /var/www/public/;
    index index.php index.html index.htm;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass laravel_cms_app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
    }
    location ~ /\.ht {
        deny all;
    }
}
```

#### Cấu hình `docker-compose.yml`
```yaml
version: '3.8'
services:
  app:
    build:
      context: ./
      dockerfile: Dockerfile
    image: laravel_cms
    container_name: laravel_cms_app
    restart: unless-stopped
    working_dir: /var/www/
    volumes:
      - ./:/var/www
    networks:
      - laravel_cms
  db:
    image: mysql:8.0
    container_name: laravel_cms_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_USER: ${DB_MYSQL_USER}
      MYSQL_PASSWORD: ${DB_MYSQL_PASSWORD}
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - laravel_cms
  nginx:
    image: nginx:alpine
    container_name: laravel_cms_nginx
    restart: unless-stopped
    ports:
      - "8081:80"
    volumes:
      - ./:/var/www
      - ./docker-compose/nginx:/etc/nginx/conf.d/
    networks:
      - laravel_cms
volumes:
  mysql_data:
networks:
  laravel_cms:
    driver: bridge
```

---

## 4. Cấu Hình RDS (Nếu Dùng AWS RDS)
- **Endpoint**: `laravel.ct8mgmqk627h.ap-southeast-1.rds.amazonaws.com`
- **File `.env` Laravel**:
```ini
DB_CONNECTION=mysql
DB_HOST=laravel.ct8mgmqk627h.ap-southeast-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=user_laravel
DB_PASSWORD=password
```

---

## 5. Cài Đặt Laravel API
```bash
cp .env.example .env
docker-compose up -d
docker exec -it --user root laravel_cms_app bash
composer install
php artisan key:generate
php artisan migrate:refresh --seed
php artisan passport:install
php artisan passport:client --personal
php artisan config:cache
php artisan cache:clear
php artisan view:clear
exit
sudo chmod -R 777 storage/
```

*Test API trên Postman hoặc trình duyệt:*
- **Trình duyệt**: `http://your-ip.com:8081`
- **Postman**: `http://your-ip.com:8081/api/register` (POST)
```json
{
    "email": "nguyennhdn@gmail.com",
    "password": "admin12345",
    "name": "hoang nguyen2121"
}
```

---

## 6. Cài Đặt Nuxt CMS
thay đổi địa chỉ IP ở các file tương ứng trong nuxt
1. nuxt.config.ts
2. authentication.ts
3. docker-compose.yml
```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```
Truy cập `http://your-ip.com:3000/signup`

---

## 7. Lệnh Hữu Ích
### Quản lý Docker
```bash
docker-compose up -d --build --force-recreate
docker-compose down
docker-compose -f docker-compose.dev.yml down
docker logs laravel_cms_app
docker logs nuxt_cms_app
docker exec -it laravel_cms_nginx sh
```
### Laravel Artisan
```bash
php artisan config:cache
php artisan passport:install
php artisan cache:clear
php artisan route:clear
php artisan make:seeder RoleAndPermissionSeeder
```

---

## 8. Xóa Docker Hoàn Toàn (Nếu Cần Reset)
```bash
docker stop $(docker ps -q)
docker rm $(docker ps -aq)
docker rmi $(docker images -q) -f
docker volume rm $(docker volume ls -q)
docker network rm $(docker network ls -q)
docker system prune -a --volumes -f
```

---

*Hướng dẫn này giúp bạn cài đặt Laravel CMS (Laravel 11 + Nuxt 3) trên EC2 một cách nhanh chóng và tối ưu.* 🚀




Để cấu hình reverse proxy trên EC2 Ubuntu cho hai source Laravel API và Nuxt 3, bạn có thể sử dụng Nginx làm reverse proxy. Dưới đây là hướng dẫn chi tiết từng bước:
Bước 1: Cài đặt Nginx
sudo apt update
sudo apt install nginx -y

Bước 2: Cấu hình Reverse Proxy cho Laravel API và Nuxt 3
sudo nano /etc/nginx/sites-available/reverse-proxy.conf
Thêm nội dung sau:
server {
listen 80;
server_name 54.251.10.44;  # Sử dụng IP để xác định server

    # Laravel API trên root path
    location /api {
        proxy_pass http://54.251.10.44:8081;  # Laravel API chạy trên port 80
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Nuxt 3 trên đường dẫn /nuxt
    location / {
        proxy_pass http://54.251.10.44:3000;  # Nuxt 3 chạy trên port 3000
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
Bước 3: Kích hoạt cấu hình Nginx
sudo ln -s /etc/nginx/sites-available/reverse-proxy.conf /etc/nginx/sites-enabled/
- Kiểm tra lỗi cấu hình Nginx:
sudo nginx -t
- Nếu không có lỗi, khởi động lại Nginx:
sudo systemctl restart nginx
sudo systemctl status nginx



