## Run in prod mode
```
cd ./docker
docker-compose down
docker-compose up -d
```

### Create api key
```
docker ps | grep 'docker-php' | awk '{print $1}'
#copy the container id and run
docker exec -it <containerId> php bin/console app:add-api-user <email>
#copy and save API key for later use
#make requests to the API http://127.0.0.1:8088/api/persons using generated API key in the header X-API-KEY
```

To receive email, add `MAILER_DSN=` to .env file and run `docker-compose up -d` again.
