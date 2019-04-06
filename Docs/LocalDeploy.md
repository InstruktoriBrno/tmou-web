# Local deployment

Tato stránka popisuje jak si rozchodit vývojovou verzi TMOU webu.

Nezbytné předpoklady jsou:
* Základní programátorské IT vzdělání, bez toho se nemá smysl do tohoto pouštět.
* GIT
* PHP alespoň ve verzi 7.1
* MySQL databázi ve verzi alespoň 5.6 (např. MariaDB 10)

1. Naklonujte si repozitář:  
   `git checkout git@github.com:InstruktoriBrno/tmou-web.git`
2. Získejte údaje pro připojení do databáze (adresa server, port, jméno, heslo, jméno databáze).  
   Pokud databázi nemáte, vytvořte si ji **s kódování `utf8mb4-czech`**!
3. Nastavte možnost zápisu do adresářů `temp` a `log`: `chmod -R 777 temp log`
4. Vytvořte lokální konfiguraci `App/Config/local.neon`, viz `local.neon.template`,
   zde nastavte údaje pro přístup k databázi, recaptchu, adresu na které web poběží...
5. Smažte obsah adresáře `temp/cache`.
6. Spusťe databázové migrace pro zajištění aktuálnosti databáze: `php bin/console migrations:migrate`
7. V rootu projektu spusťe vývojový webový server: `php -S localhost:8999 -t www`
8. Přistupte v prohlížeči na zvolenou adresu: `http://localhost:8999`

