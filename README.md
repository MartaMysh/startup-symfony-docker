Konfiguracja środowiska

Plik .env:

    APP_ENV=dev – aplikacja działa w trybie deweloperskim

    APP_SECRET – klucz używany przez Symfony do generowania tokenów i zabezpieczeń; w środowisku produkcyjnym należy ustawić silny, losowy ciąg znaków.

    DEFAULT_URI=http://localhost – podstawowy adres aplikacji.

    DATABASE_URL – konfiguracja połączenia z bazą danych PostgreSQL:
    postgresql://symfony:symfonypass@db:5432/symfony?serverVersion=15&charset=utf8

użytkownik: symfony

hasło: symfonypass

host: db (nazwa serwisu w docker-compose)

port: 5432 (wewnątrz sieci Docker)

baza: symfony

<span style="font-size: 24px;">Uruchomienie środowiska</span>

W głównym katalogu projektu dostępny jest Makefile, który automatyzuje pełną procedurę startową aplikacji.

Aby uruchomić środowisko oraz wykonać wszystkie niezbędne kroki inicjalizacyjne, użyj polecenia:

```bash
make up
```

Polecenie to:

- buduje i uruchamia kontenery Dockera,

- instaluje zależności aplikacji,

- wykonuje migracje bazy danych,

- importuje dane szkół z pliku schools.xlsx.

<span style="font-size: 24px;">Alternatywna procedura krok po kroku</span>

Jeżeli wykonanie make up zakończy się niepowodzeniem lub konieczne jest ręczne uruchomienie poszczególnych etapów,
poniżej znajduje się pełna sekwencja poleceń, które należy wykonać w podanej kolejności:

```bash
docker-compose up -d --build

docker-compose exec php composer install

docker-compose exec php php bin/console doctrine:migrations:migrate

docker-compose exec php php bin/console app:fill-schools src/School/DataFixtures/schools.xlsx
```

Każde polecenie powinno zakończyć się sukcesem przed przejściem do kolejnego kroku.

Dla testowania:

```bash
# run all tests (nie zakończone - w trakcie pracy zrozumiałam że najlepsze podejście tutaj TDD, najpierw napisać test który wykonuje sie dla wszystkich danych i na bazie tego budować Matcher)
# Oddaję kod nie zakończony.
php bin/phpunit


```

Dostęp do aplikacji

    Baza danych (Postgres):

        Host: localhost

        Port: 5434

        Użytkownik: symfony

        Hasło: symfonypass

        Baza: symfony
