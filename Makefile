migrate:
	docker compose run --rm cli bin/console doctrine:migrations:migrate