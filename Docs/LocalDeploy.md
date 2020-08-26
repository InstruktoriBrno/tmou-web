# Local deployment

Tato stránka popisuje jak si rozchodit vývojovou verzi TMOU webu.

## Předpoklady

Nezbytné předpoklady jsou:

- Základní programátorské IT vzdělání, bez toho se nemá smysl do tohoto pouštět.
- Funkční Docker a Docker Compose (příkazy `docker` a `docker-compose` na PATH).
- Doména `tmou.test` nasměrovaná na IP adresu na které budou tunelovány Docker kontejnery (via `/etc/hosts`).
  Na Linuxu to bude typicky `127.0.0.1` nebo `127.0.x.x` pokud používáte více Docker kompozic.
  Na OS X to bude buď stejné jako na Linuxu (může být pomalé), nebo třeba `192.168.99.x` pokud používáte Docker Machine s VirtualBoxem.
  Tuto IP taktéž nastavte do souboru `.env` do proměnné `IP` (bez toho se kompozice nespustí).
  Dále v tomto souboru nastavte `PROJECT_DIR` na cestu k projektu, `.` postačí pro většinu použití (jen na OS X při použití NFS je potřeba absolutní cesta začínající `/System/Volumes/Data/`).
- Pro spuštění Tracy ve vývojovém režimu potřebujete buď přidat adresu do `Booting.php`, nebo spouštět docker kompozici s ENV proměnou `TRACY_DEBUG_ENABLE` nastavenou na `1` (mělo by být nastaveno jako výchozí).

## Postup

1. Naklonujte si repozitář:  
   `git checkout git@github.com:InstruktoriBrno/tmou-web.git`
2. Vytvořte (pokud neexistuje) adresář `.mysql` a dejte mu všechna oprávnění via `chmod 777 .mysql` (relevantní pro Linux, jinak bude mít Docker problém tam zapisovat).
3. Spusťte `docker-compose up`, nebo `docker-compose up -d` (odpojí se od terminálu). V případě, že se změnil hlavní `Dockerfile` sestavte nový kontejner `docker-compose up --build`.
4. Počkejte na doběhnutí startu všech Docker kontejnerů, neměla by se objevit žádná chyba.
5. Přihlašte se z vedlejší konzole do Docker kontejneru `webserver` pomocí příkazu `docker-compose exec webserver bash`.
6. Uvnitř Docker kontejneru `webserver` nainstalujte Composer závislosti `composer install`.
7. Uvnitř Docker kontejneru `webserver` spusťte (po úplném naběhnutí databáze -- v logu kontejneru `webserver` je `mysqld is alive`) databázové migrace pro zajištění aktuálnosti databáze: `php bin/console migrations:migrate`
8. Nyní můžete vše používat:
   - TMOU Web: http://tmou.test a https://tmou.test (primární vývoj probíhá skrze HTTPS, kvůli přihlašování)
   - Adminer: http://tmou.test:8080
   - Keycloak: https://tmou.test:9990
9. Pro ukončení kompozice `docker-compose stop` nebo CTRL-C pokud je spuštěna na popředí.
   Opětovné volání `docker-compose up` spustí předchozí stav.  
   Úplné smazání kontejnerů lze provést pomocí `docker-compose down`.  
   Kompletní rebuild kontejnerů lze provést pomocí `docker-compose up --build`.
   
## Testovací data

Pro jednoduchost vývoje v delším časovém horizontu je připraveno automatizované vytváření dat.
Tyto data lze vytvářet pouze na existující databázi ve které ale nejsou žádná data (vyjma již přihlášených organizátorů).
Po vytvoření migrací je třeba zevnitř kontejneru `docker-compose exec webserver bash` spustit `php bin/console create-test-data`.

Toto vytvoří:
- Ročník v aktuálním roce se hrou první víkend v listopadu a kvalifikací poslední zářiový víkend.
- Vytvoří standardní sadu stránek & menu ročníku.
- Vytvoří 4 týmy.
- Vytvoří 2 diskuzní vlákna.

## Přístup k databázi

Databáze je v samostatném Docker kontejneru. Datové soubory jsou uloženy v adresáři `.mysql`, pokud je
potřeba restartovat obsah databáze, stačí smazat obsah tohoto adresáře.

Dodatečná konfigurace databáze může probíhat skrze `.cnf` soubory v adresáři `.mysql-config`.

Pro přístup do databáze skrze UI je v kompozici přítomen Docker kontejner s [Adminerem](https://www.adminer.org/cs/),
jeho adresa v kompozici je http://tmou.test:8080.

Přihlašovací údaje jsou:

- Jméno: `tmou`
- Heslo: `password`

## Správa uživatelů v Keycloaku

Keycloak je nakonfigurován v samostatném Docker kontejneru a jeho administrační rozhraní je přístupné na adrese https://tmou.test:9990.

Přihlašovací údaje jsou:

- Jméno: `admin`
- E-mail: `admin@example.com`
- Heslo: `admin`

Uživatelé:

- `<username>:<password> <email>`
- `admin:admin admin@example.com`
- `tmou1:tmou1 tmou1@example.com`
- `tmou2:tmou2 tmou2@example.com`
- `tmou3:tmou3 tmou3@example.com`
- `netmou1:netmou1 netmou1@example.com`
- `netmou2:netmou2 netmou2@example.com`

URL pro autentizaci tmou:

- URL: `keycloak`
- Realm: `Instruktoři Brno`
- Client ID: `tmou-web-local`
- Client Secret: `e7307d96-71e4-4c7a-a626-0bc2ac4eef66`

## Aktualizace (přegenerování) uloženého Keycloak realmu

V případě, že je potřeba vygenerovat nový export dat z realmu nelze použít export z administračního rozhraní,
protože neobsahuje secret tokeny, ani uživatele, viz [dokumentace](https://access.redhat.com/documentation/en-us/red_hat_single_sign-on/7.0/html/server_administration_guide/export_import).

1. Nahoďte docker kompozici, viz výše.
2. Připojte se na administrační rozhraní, viz výše.
3. Proveďte požadované změny.
4. Přihlašte se do příslušného Docker kontejneru `docker-compose exec keycloak bash`.
5. a z adresáře `/opt/jboss/keycloak` spusťte následující příkaz:

    ```bash
    bin/standalone.sh -Dkeycloak.migration.action=export -Dkeycloak.migration.provider=singleFile -Dkeycloak.migration.file=realm-export.json -Djboss.http.port=8888 -Djboss.https.port=9999 -Djboss.management.http.port=7777
    ```
6. Po úspěšném doběhnutí (a ukončení pomocí CTRL-C) zkopírujte obsah souboru `realm-export.json` do `.keycloak/realm-export.json`.
   ```bash
   docker cp <CONTAINER_ID>:/opt/jboss/keycloak/realm-export.json .keycloak/realm-export.json
   ```
6. Z daného souboru odstraňte konfiguraci pro realm `master` a namísto pole ponechte jen objekt realmu `Instruktoři Brno`.
7. Otestujte novou konfiguraci.
