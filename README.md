Hệ thống quản lý nội dung đa người dùng (CMS) use Laravel 11 with nuxt3 with laravel passport and pinia and shadcn-vue 
Back End : 

B1. clone source  
B2. cp .env.example .env    
B3. docker-compose up -d   
B4. docker exec -it --user root  laravel_cms_app bash  
B5. composer install   
B6. php artisan key:generate  
B7. php artisan migrate

rebuild docker image :docker-compose up -d --build --force-recreate  
docker-compose -f docker-compose.dev.yml build --no-cache  
docker-compose -f docker-compose.dev.yml up -d  
docker-compose -f docker-compose.dev.yml down  

php artisan config:cache  
php artisan cache:clear  
php artisan passport:client --personal  
------------------production-------------------------  
docker-compose -f docker-compose.prod.yml up -d --build  

Front  End
## Setup

## Use node version v20.18.1, and shadcn-vue and taiwindcss for nuxt

Make sure to install dependencies:

```bash
# npm
npm install
```bash
# npm
npm run dev
