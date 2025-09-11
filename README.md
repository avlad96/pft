## Требования

- PHP >= 8.2
- MySQL >= 8.0
- Composer >= 2.x
- Docker & Docker Compose (для локальной разработки с Sail)

## API документация

В проекте подключена документация с использованием [Scribe](https://github.com/knuckleswtf/scribe). Доступна по адресу:

```
/api/docs
```

## Тестирование

Для запуска тестов в локальной среде с Sail выполнить:

```
./vendor/bin/sail artisan test
```

## Локальная разработка (с Sail)

1. Клонировать репозиторий:

```
git clone https://github.com/avlad96/pft.git
```

2. Запустить Sail и собрать контейнеры:

```
./vendor/bin/sail up -d
```

3. Установить зависимости:

```
./vendor/bin/sail composer install
```

4. Создать `.env` файл и настроить параметры окружения:

```
cp .env.example .env
```

5. Сгенерировать ключ приложения:

```
./vendor/bin/sail artisan key:generate
```

6. Запустить миграции:

```
./vendor/bin/sail artisan migrate
```

## Ручная установка

1. Клонировать репозиторий:

```
git clone https://github.com/avlad96/pft.git
```

2. Установить зависимости:

```
composer install
```

3. Создать `.env` файл и настроить параметры окружения:

```
cp .env.example .env
```

4. Сгенерировать ключ приложения:

```
php artisan key:generate
```

5. Запустить миграции:

```
php artisan migrate
```
