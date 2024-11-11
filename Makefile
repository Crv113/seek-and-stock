start:
	docker-compose up --build

dstart:
	docker-compose up --build -d

stop:
	docker-compose down

vstop:
	docker-compose down -v

migrate:
	docker-compose exec app php artisan migrate

check-results:
	docker-compose exec app php artisan check-new-results


