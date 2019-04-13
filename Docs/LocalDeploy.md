# Local deployment

Tato stránka popisuje jak si rozchodit vývojovou verzi TMOU webu.

Nezbytné předpoklady jsou:
* Základní programátorské IT vzdělání, bez toho se nemá smysl do tohoto pouštět.
* GIT
* PHP alespoň ve verzi 7.1
* MySQL databázi ve verzi alespoň 5.6 (např. MariaDB 10)
* Nainstalovaný balíčkovací nástroj [Composer](https://getcomposer.org/) na PATH.

1. Naklonujte si repozitář:  
   `git checkout git@github.com:InstruktoriBrno/tmou-web.git`
2. Nainstalujte závislosti spuštěním `composer install` v rootu webu.
3. Získejte údaje pro připojení do databáze (adresa server, port, jméno, heslo, jméno databáze).  
   Pokud databázi nemáte, vytvořte si ji **s kódování `utf8mb4-czech`**!
4. Nastavte možnost zápisu do adresářů `temp` a `log`: `chmod -R 777 temp log`
5. Vytvořte lokální konfiguraci `App/Config/local.neon`, viz `local.neon.template`,
   zde nastavte údaje pro přístup k databázi, recaptchu, adresu na které web poběží...
6. Smažte obsah adresáře `temp/cache`.
7. Spusťe databázové migrace pro zajištění aktuálnosti databáze: `php bin/console migrations:migrate`
8. V rootu projektu spusťe vývojový webový server: `php -S localhost:8999 -t www`
9. Přistupte v prohlížeči na zvolenou adresu: `http://localhost:8999`

## Composer

Pokud nemáte composer na PATH, nebo v systému máte příliš starý, můžete vyřešit problém lokální instalací
(resp. stažením souboru PHAR a jeho spouštěním), viz [návod](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos).

Poté namísto `composer <action>` spouštíte `php composer.phar <action>`. Oba způsoby jsou ekvivalentní.
