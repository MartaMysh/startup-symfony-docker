up:
	docker-compose up -d --build
	docker-compose exec php composer install
	docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
	docker-compose exec php php bin/console app:fill-schools src/School/DataFixtures/schools.xlsx

restart:
	docker-compose down
	make up
