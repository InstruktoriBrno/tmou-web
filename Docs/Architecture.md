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

Stylování aplikace je realizováno skrze knihovnu [Tailwind](https://tailwindcss.com/), vlastní styly a preprocessing.

Šablona stylopisu (což je soubor, který se edituje vývojáři) je v `resources/css/app.css`. Preprocessing se spouští v kořenovém
adresáři projektu `npm run compile-css` (předtím je potřeba spustit zde `npm install`). Preprocesovaný a minifikovaný styl
je k nalezení v `assets/css/app.css`.

Potřebujete-li vlastní nové CSS třídy, přidávate je do `resources/css/app.css` do části mezi `/*! purgecss start ignore */` a `/*! purgecss end ignore */` (tím by měly být vždy zachovány po preprocessinug).

Processing je poněkud komplikovanější, takže zde jsou důležité scénáře:

1. Potřebujete v nějaké Latte šabloně změnit styl v rámci běžně vypadajícího HTML. V takovém případě přidáte příslušnou třídu podle Tailwind do `class` příslušného elementu.
   Při preprocessingu Tailwind (resp PurgeCSS) najde použití této CSS třídy a převede ji i do finálního minifikovaného CSS.
2. Potřebujete v nějaké Latte šabloně změnit styl v rámci nějakých Latte podmínek, maker atp. V takovém případě příslušná třída nemusí být nalezena automaticky (ať už jde o vaši třídu či z knihovny)
   Pokud se to stane, přidejte tuto třídu do `postcss.config.js` whitelistu.
3. Používáte v PHP kódu konkrétní třídy z knihovny Tailwind (či vlastně dodefinované), které se ale jinde nepoužívají a preprocessing je nemůže v PHP kódu nalézt (neprohledává jej). V takovém případě (pokud nejsou použity ani jinde)
   by preprocessing třídy odstranil, lze je tedy přidat do whitelistu (což ale nefunguje například pro stylopisy `select {}`), nebo je tedy potřeba zařídit někde na stránce (v rámci Latte šablon) jejich použití
   nebo příslušné stylování přidat přímo na těchto elementech (z čistého stavu), tak aby výsledný stav byl stejný. 

Po každé změně samozřejmě následuje rekompilace stylopisu, viz výše.

Protože formuláře a datagrid původně počítaly se stylováním Boostrapu, jsou příslušné třídy nastylované v `resources/css/app.css` pomocí původních CSS tříd.

## Single Sign On (SSO)

Systémy pro kvalifikaci a hru nejsou na rozhodnutí týmu součástí webové prezentace a jsou jako samostatné projekty.
Z toho plyne potřeba jednotného jednoduchého přihlášení, které je realizováno na TMOU skrze SSO cookie s náhodně generovaným tokenem.
Cookie je vystavována v režimu `Strict`, `HTTP only` a `SameSite=Strict` režimu a to pro doménu dle konfigurace (viz klíč `sso.cookieDomain`, který by měl být vždy přepsán).

Z toho plyne omezení, že není možné na žádné subdoméně této domény provozovat neověřený či nebezpečný obsah, protože taková konfigurace není bezpečná.

Jeden tým může mít přihlášeno i více tokenů (tedy více uživatelů z jednoho týmu na více zařízeních), při odhlášení se maže token z daného zařízení, je-li k dispozici.

Tyto jednoduché cookie je potřeba verifikovat, což lze se historicky dělalo přímým přístupem do databáze, což se ale ukázalo jako nevhodné řešení z výkonnostních důvodů.
Z tohoto důvodu se vystavuje též druhá cookie (pod názvem `sso.jwtCookieName`) opět v režimu `Strict`, `HTTP only` a `SameSite=Strict` ve které je uložen JWT token.
Existuje též rozhraní `/api/verify-sso?token=<TOKEN>&jwt=<JWT>`, kde lze ověřit zda je příslušné SSO ještě validní, v případě, že ale dotaz z nějakého důvodu selže, mělo by se předpokládat, že tomu tak je.

Platnost SSO by měla být omezenější než přihlášení do webu (tedy méně než 14 dní). Stránka ale kontroluje při přístupu zda již token není expirovaný či jinak vadný
a pokud tato eventualita nastane vystaví token nový.

Jednou za čas je potřeba promazat uložené tokeny pomocí příkazu `bin/console clean-sso-sessions`.

V rámci SSO JWT tokenu je k dispozici: `tid` s ID týmu, `tno` s číslem týmu v rámci ročníku, `tna` s názvem týmu.

## Pravidelné úlohy (CRON) 

V obecnosti jsou dva způsoby realizace pravidelné úlohy. Lze je spouštět buď přistoupením na adresu s tokenem, nebo spouštět rovnou z příkazové řádky.
CRON umí být nastaven na oba způsoby, avšak zde je vybrán první způsob z důvodu problémů, které by způsobilo spouštění pod jiným uživatelem než `www-data`.

Jednotlivé invokovatelné akce jsou definovány v presenteru `InstruktoriBrno\TMOU\Presenters\CronPresenter`. 
