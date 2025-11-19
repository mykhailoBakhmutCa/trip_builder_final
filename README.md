# Trip Builder

A simple web app for searching flights and building trips. Made with Laravel and MySQL. Uses Docker for running everything.

## How to Run

1. Clone the repo  
```
git clone <YOUR_REPO_URL>
cd <PROJECT_DIR>
```

2. Copy env file  
```
cp .env.example .env
```

3. Start Docker  
```
docker compose up -d --build
```

4. Install PHP packages  
```
docker compose exec app composer install
```

5. Generate app key  
```
docker compose exec app php artisan key:generate
```

6. Run migrations and seeds  
```
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

## Open the App

Go to:  
http://localhost:8080/

## Features

- One-way and round-trip search  
- Sorting by price or duration  
- Simple timezone handling  
- Basic filters  

## Tests

```
docker compose exec app php artisan test
```
