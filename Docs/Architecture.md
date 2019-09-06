# Architektura

Web používá nejpoužívanější MVC framework v česku, [Nette](https://nette.org/cs/). Ten definuje základní architekturu
celé aplikace.
 
Zjednodušeně řečeno jde o to, že je zde instance aplikace, která zpracovává všechny HTTP (a CLI) requesty
které přetaví do aplikačních requestů.

Tyto aplikační requesty jsou prohnány skrze routovací tabulku a je pro ně nalezena dvojice presenteru a metody v něm (akce),
která je obslouží. Tento presenter je instanciován, metoda zavolána s příslušnými parametry a její provedení je obsluhou
daného aplikačního requestu.

Výsledkem zpracování může být přesměrování, zaslání souboru atp... Pokud nic z toho programátor neprovede, přistoupí se
k automatickému renderování přidružené šablony.

Pro lepší pochopení je vhodné projít si základní tutorial v [dokumentaci](https://doc.nette.org/cs/3.0/quickstart).

## Modelová vrstva

Pro modelovou vrstvu se používá [Doctrine](), což je nejrozšířenější ORM knihovna ve světě PHP. Zajišťuje
mapování jednotlivých řádků tabulky objektů a vazeb mezi nimi.

Do databáze by se nemělo přistupovat jinak než skrze toto mapování, případně skrz podřízenou DBAL vrstvu, v případě
analytických a dávkových zpracování.

Spolu s databází je zde přítomen i nástroj pro spouštění migrací databáze na novou verzi. Jednotlivé migrace jsou
v adresáři `App/Migrations` a spouštění se pomocí `php bin/console migrations:<command>`, je zde také přítomná utilita
pro  usnadnění generování migrací `php bin/console orm:<command>`.

Pro lepší dokumentaci je vhodné projít si základní tutorial v [dokumentaci](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/tutorials/getting-started.html).

Pro plnou kompatibilitu s Unicode je potřeba všechny sloupce explicitně vytvářet v `utf8mb4` kódování. (Výjimkou může být akorát sloupce do kterého půjdou pouze ASCII hodnoty a je potřeba využití maximální délku kvůli MySQL UNIQUE omezení.)

## Struktura aplikace

Architektury daného frameworku ponechává stále podstatnou volnost jak implementovat zpracování požadavku, nicméně
úvodní provedení aplikace zvolilo následující strukturu a zásady:

- Autoloading vyžaduje správné strukturování složek aplikace, které musí odpovídat namespacům.
- Jednotlivé presentery jsou ve složce `App/Presenters` a jejich název je striktně daný jako `<Foo>Presenter`. Příslušné
  šablony jsou v podsložce `templates/<Foo>/<action>.latte`
- Nette poskytuje komponenty, jakožto jednotku použitelnosti. Rozlišují se formuláře (`App/Forms`), gridy (`App/Grids`) a obecné komponenty (`App/Components`).
  Jednotlivé komponenty nesmí uvnitř provádět žádnou logiku ohledně zpracování dat, to by mělo být vždy transparentně zařízeno v příslušném presenteru pomocí callbacků.
- Jednotlivé ORM entity patří do složky `App/Model`, k definici mapování na tabulky se používají anotace.
- Všechny routy by měly být definovány ve třídě `RouterFactory`, na pořadí záleží (nejspecifičtější nahoře).
- Zpracování požadavku provádí Presenter a je dále děleno na fasády (které zastřešují jednu konkrétní akci, například `RegisterNewTeamFacade`),
  fasádá ke splnění svého use casu používá dostupné služby (z DI kontejneru), které by měly být pro lepší zapouzdření, čitelnost a udržitelnost
  vyčleňovány do samostatných servis (například `CreateTeamServise`, `SendTeamNotificatiton`...). Pravidlem je, že
  jednotlivé servisy nikdy neukládají nic přímo do databáze, maximálně říkají, co by mělo být uloženo (Doctrine `persist`), samotné uložení provádí
  vždy a jen fasáda (Doctriner `flush`).
- Throwable se nezachycují (padají sem i syntaktické chyby).
- Výjimky se používají! Chyby v logice, porušené předpoklady, které měl zajistit programátor
  mají vyhazovat `InstruktoriBrno\TMOU\Exceptions\LogicException` (vhodně pojmenovaného potomka!). Chyby, ke kterým došlo kvůli konstelaci světa (např. výpadek DB během zpracování requestu) mají být přetaveny do `InstruktoriBrno\TMOU\Exceptions\RuntimeException` (vhodně pojmenovaného potomka!).
  Chyby, se kterými se počítá (například špatné vstupy od uživatelů...) a které by měly být ošetřeny, by měly dědit od `InstruktoriBrno\TMOU\Exceptions\CheckedException`.
  Používejte správné logovací úrovně při manuálním logování skrze `Debugger::log()`. 

## Dependency Injection

Protože jednotlivé části kódu závisí na jiných (například spojení v databázi), je zde přítomen DI kontejner
(viz `Booting.php` a `Config` složka), který zajišťuje vytvoření kontejneru se všemi základními službami a se schopností
vytvořit na počkání ty, které budou zrovna při zpracování potřeba.

## Latte šablony

Pro šablony se zde používá s Nette spjatý šablonovací jazyk [Latte](https://latte.nette.org/cs/), který má svou
vlastní [syntaxi](https://latte.nette.org/cs/guide).

Jednou z výhod je možnost skládání šablon pomocí `@layout.latte` a pomocí zanořování bloků.

## Tracy

Během vývoje (nikoliv však na produkci) je dostupná Tracy, která každou chybu přetaví do laděnky (aka BlueScreen),
jednotlivé chybové hlášky (a to platí i pro produkci) jsou uloženy ve složce `logs`.

## Kontrola oprávnění

Nette poskytuje základní kontrolu oprávnění postavenou nad Role-Based-Access-Control návrhovým vzorem.
Ve zkratce to znamená, že v aplikací existují *resourcy*, se kterými můžou uživatelé různých *rolí* provádět různé *akce*.

- Resourcy: `InstruktoriBrno\TMOU\Enums\Resources`, například správa uživatelů, správa ročníků, ...
- Akce: `InstruktoriBrno\TMOU\Enums\Actions`, například `VIEW`, `LOGIN`, ...
- Role: `InstruktoriBrno\TMOU\Enums\UserRole`, tyto role neodpovídají rolím Organizátora, ale víceméně odlišují různý typy uživatelů (nepřihlášený a z něho dědící tým, respektive organizátor).

Celé nastavení oprávnění je ve třídě `InstruktoriBrno\TMOU\Application\Authorizator`.

Pro kontrolu oprávnění v aplikaci se používají anotace (oživené v `InstruktoriBrno\TMOU\Presenters\BasePresenter`, který je potřeba používat jako univerzálního předka všech presenterů), viz například:

```php
    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_ORGANIZATORS,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionOrganizators() {}
```

```php
    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_ORGANIZATORS,InstruktoriBrno\TMOU\Enums\Action::VIEW, Jetasys\Papilio\Enums\PrivilegeEnforceMethod::NOT_AVAILABLE) */
    public function actionOrganizators() {}
```

## Práce se styly

TBD
