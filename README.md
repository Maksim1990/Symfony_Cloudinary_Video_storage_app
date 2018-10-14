# Symfony_Cloudinary_cloud_storage_app
Symfony 4 and Cloudinary cloud storage app

###### Detailed description of this project: http://web.discoveringworld.net/projects/symfony-cloudinary-api-video-storage-app/

###### Check out also my other applications and works: http://web.discoveringworld.net

---
### 1) Clone current repository:
```
git clone https://github.com/Maksim1990/Symfony_Cloudinary_Video_storage_app.git [APP_NAME]
```
### 2) Navigate to the clonned derictory
```
cd [APP_NAME]
```
### 3) Install required Composer dependencies:
```
composer install
```
### 4) Rename environment config file:
```
cp .env.dist .env
```
### 5) In .env file fill in correct data for DATABASE_URL variable with relevant credentials data
### 6) Generate database that were specified in *DATABASE_URL* variable in *.env* file
```
php bin/console doctrine:database:create

```
### 7) Go to [Cloudinary](https://cloudinary.com) and create new free account
### 8) In *.env* file insert required keys for Cloudinary account
```
CLOUD_NAME=***************
API_KEY=***************
API_SECRET=***************
```
### 9) Generate required migrations and migrate it
```
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```
### 10) Run Symfony built-in development server
```
php bin/console server:run
```
### 11) Navigate in browser to [http://127.0.0.1:8000](http://127.0.0.1:8000) and enjoy the project!
---
