# Production deploy

Víceméně stejné jako při nasazení [pro lokální vývoj](LocalDeploy.md).

Odlišnosti jsou:
 - Je obzvláště vhodné zazálohovat celou databázi před začátkem migračního procesu, soubory by měly být verzované.
 - Během nasazení je vhodné aby stránka byla mimo provoz (typicky nějakým přesměrováním v `.htaccess` vyjma povolené IP).
 - Smazání keše je nezbytné, v produkčním režimu se sama přegenerovává jen pokud chybí!
 - Instalaci závislostí je vhodné spouštět s dodatečnými parametry: `composer install --no-dev --optimize-autoloader --no-progress --no-interaction --no-suggest`
