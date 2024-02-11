.PHONY: \
	db


db:
	docker stop interview-postgres || true && docker rm interview-postgres || true
	docker volume rm interview_postgres_data || true

	docker volume create interview_postgres_data
	docker run --name interview-postgres \
		-e POSTGRES_DB=interview \
		-e POSTGRES_USER=interview \
		-e POSTGRES_PASSWORD=test123 \
		-v interview_postgres_data:/var/lib/postgresql/data \
		-p 5732:5432 \
		--restart=unless-stopped \
		-d postgres:15.2-alpine

	docker run --link interview-postgres:interview-postgres --rm martin/wait -p 5432
	bin/console doctrine:migrations:migrate --no-interaction

db_test:
	docker stop interview-test-postgres || true && docker rm interview-test-postgres || true
	docker run --name interview-test-postgres \
		-e POSTGRES_DB=interview_test \
		-e POSTGRES_USER=interview \
		-e POSTGRES_PASSWORD=test123 \
		-p 5735:5432 \
		--restart=unless-stopped \
		-d postgres:15.2-alpine

rabbitmq:
	docker stop interview-rabbitmq || true; docker rm interview-rabbitmq || true
	docker run --name interview-rabbitmq \
		-e RABBITMQ_DEFAULT_USER=guest \
		-e RABBITMQ_DEFAULT_PASS=guest \
		-p 5668:5672 \
		-p 15668:15672 \
		--restart=unless-stopped \
		-d rabbitmq:3.8-management-alpine
