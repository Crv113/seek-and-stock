start:
	docker-compose up --build -d

stop:
	docker-compose down

migrate:
	docker-compose exec app php artisan migrate

check-results:
	docker-compose exec app php artisan check-new-results


