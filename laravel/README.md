1) собрать контейнеры `docker compose up -d --build`
2) отредактировать `.env` файл (как минимум прописать бд и редис, в качестве хоста указывать название контейнера)
3) выполнить `composer install` (php контейнер)
4) выполнить миграции `php artisan migrate` (php контейнер)
5) выполнить сидер для тестов `php artisan db:seed` (php контейнер)
6) запросы можно выполнять через postman или curl:

получение слотов:
`curl http://127.0.0.1/api/slots/availability`

холд слота (указывать id слота)
`curl -X POST http://127.0.0.1/api/slots/1/hold -H "Idempotency-Key: 123e4567-e89b-12d3-a456-426614174000"`

подтверждение холда (указывать id холда)
`curl -X POST http://127.0.0.1/api/holds/100/confirm`

отмена холда (указывать id холда)
`curl -X DELETE http://127.0.0.1/api/holds/100`
