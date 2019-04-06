# Pre commit kontrola

Kontrola statickou analýzou a kontrola code style lze spustit naráz pomocí `bin/checks.sh`.

# Statická analýza

Slouží k odchycení potenciálních chyb v kódu při vývoji bez jeho spuštění, používá se k tomu nástroj [PHPStan](https://github.com/phpstan/phpstan)
s maximální možnou podrobností/pedantností.

Kromě základní sady pravidel jsou přidány ještě (viz `phpstan.neon`):
- Striktní pravidla
- Pravidla pro Nette specifika
- Pravidla pro Doctrine specifika

Lze jej spouštět z rootu projektu:

`vendor/bin/phpstan analyse --level 7 App Tests`

Při mergování musí být všechny potenciální problémy odstraněny. Postupný nárůst a ignorování problémů nejsou přípustné.

# Code style

Používaný code style je [PSR-2](https://www.php-fig.org/psr/psr-2/), který v sobě implicitně zahrnuje [PSR-1](https://www.php-fig.org/psr/psr-1/).
Pro kontrolu se používá nástroj [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer).

Tento je drobně upraven v souboru `ruleset.xml`, hlavně po stránce zakázaných (zastaralých a chybových) konstrukcí, a sjednocení formátování tříd.

Lze jej automatizovaně kontrolovat spuštěním následujícího příkazu z rootu projektu:

`./vendor/bin/phpcs --standard=ruleset.xml App/ Tests/`

Některé chyby jsou opravitelné automatizovaně pomocí přidružené utility:

`./vendor/bin/phpcbf --standard=ruleset.xml App/ Tests/`

Při mergování musí být všechny problémy code style odstraněny. Postupný nárůst a ignorování problémů nejsou přípustné.

# Best practices & hints

## Správa závislostí

Závislosti (knihovny...) používané touto aplikací spravuje [Composer](https://getcomposer.org), jejich konkrétní výčet
je uložen v souboru `composer.json`.

Použivejte composer, extra přidané knihovny věstí bezpečnostní problémy.

## Autoloading tříd

Jednotlivé referované třídy (ať už naše nebo z knihoven) jsou načítány pomocí autoloadingu, který zařizuje Composer (viz výše) a konfiguruje se v `composer.json`.

Pro správné použití je nezbytné správně pojmenovávat jednotlivé adresáře (case sensitive!) a soubory podle konvence [PSR-4](https://www.php-fig.org/psr/psr-4/),
tzn. vše ve složce `App` musí být v namespacu `InstruktoriBrno\TMOU` a následně každá část namespacu odpovídá názvu složky, název třídy pak názvu souboru (+ přípona).

## Lokální konfigurace

Je v souboru `App/Config/local.neon`. Nesmí být verzována, pokud unikne, je potřeba všechny hesla a klíče změnit.
V repozitáři je verzovaná její `.template` varianta, ve které se lze inspirovat.

## Správa assetů (css, JS, obrázky...)

Jsou ve `www/assets/` rozdělené podle typu. Nejsou nijak preprocesována.

## Způsob získávání závislostí v aplikaci

V případě presenterů získávejte závislost skrze *property injection*:

```php
/** @var Foo @inject */
public $foo; // must be public, otherwise injection will fail
```

V případě komponent získávejte závislosti skrze *constructor property*:

```php
public function __construct(Foo $foo) { ... }
``` 

## Vytváření formulářů

Vždy formuláře vytvářejte skrze `InstruktoriBrno\TMOU\Forms\FormFactory`, ta zařizuje základní chování

## Veřejné a citlivé formuláře

U formulářů, které umožňují registrace, žádosti o změny hesel, či změny hesel, či vkládají nějaký obsah (veřejně nepřihlášeně),
nezapomeňte přidat ochranu proti CSRF útoku:

```php
$form->addProtection('Platnost formuláře vypršela, odešlete jej, prosím ještě jednou.');
```

A ideálně u veřejných i kontrolu proti botům

```php
$form->addInvisibleReCaptcha('recaptcha')
    ->setMessage('Jste opravdu člověk?');
```

## Odesílání e-mailů

K odesílání e-mailů je nakonfigurovaná služba pod rozhraním `IMailer`, kterou si lze vyžádat via DI.
Nepoužívejte jinou službu k odesílání e-mailů (viz další bod).

Ve vývojovém prostředí si lze pomocí lokální konfigurace aktivovat MailPanel a zachytávat do něj všechny e-maily.
Při každém vývoji si ale nejprve vyzkoušejte jeho funkčnost na nějaké své e-mailové adrese.

## Přístup do databáze

Pro jednoduchost přístup do databáze je ve vývojovém režimu k dispozici na adrese `/adminer/` jednoduché UI.
Viz [Adminer](https://www.adminer.org/cs/).


