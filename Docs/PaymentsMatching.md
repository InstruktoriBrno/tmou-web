# Automatizované párování plateb

Automatizované párování plateb vyžaduje:
 - Správné nastavení výše startovného a deadlinu platby u příslušného ročníku.
 - Nakonfigurovaný a pravidelně automaticky spouštěný párovač (v `App/Config/local.latte`), viz [produkční nasazení](ProductionDeploy.md).
 

Jak párovač funguje:
 - Získání všech ročníků u kterých je deadline na placení od včerejška (včetně) dále. Po vypršení tohoto deadlinu je nutné všechny platby párovat ručně!
 - Stáhne se výpis účtu a projdou se všechny transakce.
 - Pro každou transakci se vyzkouší párování dle prefixu příslušného ročníku a vyextrahuje se číslo týmu, tento tým se vyhledá.
 - Proběhne samotné nastavení platby a to pouze v případě, že tým je v momentu párování Kvalifikován a zároveň nemá zaplaceno a zároveň poslal částku rovnu či vyšší startovnému (to je jediný úspěšný stav).
   Všechny ostatní stavy jsou považovány za potenciální problém, byť být problém samozřejmě nemusí.

Další poznámky:
 - Po vypršení deadlinu pro platbu v příslušném ročníku je nutné všechny platby párovat ručně! Automatické párování plateb bude už tento ročník ignorovat.
 - V případě, že je potřeba pozornost, systém o tom pošle e-mailovou notifikace dle konfigurace a to per párovaná událost.
 - Logy párování plateb si lze prohlížet v administraci. Nejstarší záznamy jsou dole. Zvýrazněny jsou červené (pozornost vyžadující) a zelené záznamy (úspěšná párování).
 - Nespouštějte párování plateb na již spárovaném období! Dostanete tak falešné poplachy o duplicitních platbách atp.
 - Nespouštějte párování plateb na dnešek, data nejsou úplná, opět můžete dostat falešné poplachy o duplicitních platbách v budoucnosti atp.
 - Období je definováno od - do, oba dny jsou v datech zahrnuty (tzn. 2019-10-09 - 2019-10-09 je jeden den),
