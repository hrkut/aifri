# Nasadenie na produkciu

Návod na nasadenie zmien z 15. 11. 2026: ročníky (2. ročník 2. 2. 2027), posudzovanie príspevkov,
anglická verzia webu, nový dizajn, e-maily účastníkom, PDF programu, oprava prístupu k záznamom
a prechod na Laravel 13.

Postup je overený na kópii ostrej databázy: všetkých 5 nových migrácií prešlo a web, administrácia,
registrácia, posudzovanie, otázky aj záznamy fungovali nad ostrými dátami.

V príkladoch je aplikácia v `/var/www/aifri` a web server beží pod používateľom `www-data`.
Upravte podľa servera.

---

## 1. Požiadavky na server

| Čo | Požiadavka |
|---|---|
| PHP | **8.4 alebo novšie** (Laravel 13; doteraz stačilo 8.2) |
| PHP rozšírenia | ctype, dom, fileinfo, filter, gd, hash, iconv, json, libxml, mbstring, openssl, pcre, session, simplexml, tokenizer, xml, xmlreader, xmlwriter, zip, zlib, pdo_mysql |
| odporúčané | **intl** – slovenské abecedné radenie prezenčnej listiny (Č za C, Š za S); bez neho sa radí jednoduchšie |
| Composer | 2.x |
| Node.js | 20.19+ alebo 22.12+ (len na zostavenie CSS/JS administrácie; dá sa to urobiť aj mimo servera, pozri krok 6) |
| MySQL | bez zmeny |

Kontrola:

```bash
php -v
php -m | grep -E -i 'intl|gd|zip|mbstring|pdo_mysql'
cd /var/www/aifri && composer check-platform-reqs --no-dev   # po kroku 4
```

Fronta (queue) ani cron nie sú potrebné, e-maily sa posielajú hneď.

## 2. Záloha

```bash
cd /var/www/aifri
git rev-parse HEAD > ~/aifri-pred-nasadenim.commit      # na návrat kódu
cp .env ~/aifri-env-$(date +%F).bak
mysqldump --single-transaction --no-tablespaces -u UZIVATEL -p NAZOV_DB > ~/aifri-db-$(date +%F).sql
```

Videá a prezentácie v `storage/app/private/zaznamy` a `storage/app/private/prezentacie` nie sú
v gite. Nasadenie sa ich nedotkne, ale ak ich nemáte zálohované inde, zálohujte aj tieto adresáre.

## 3. Údržbový režim

```bash
sudo -u www-data php artisan down
```

## 4. Nový kód a balíčky

```bash
git fetch origin
git checkout main
git pull --ff-only origin main
sudo -u www-data composer install --no-dev --optimize-autoloader
```

Ak sa kód na server nekopíruje cez git, zmažte ručne staré ladiace súbory, ktoré boli odstránené:
`public/konferenciaAI.html`, `public/recordings-test.html`, `public/session-debug.php`,
`public/test-direct-link.html`, `public/test-session.html`.

## 5. Úprava `.env`

Porovnajte s `.env.example`. Na produkcii musí byť:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://VEREJNA-ADRESA-WEBU     # používa sa v odkazoch v e-mailoch

APP_LOCALE=sk
APP_FALLBACK_LOCALE=en
# APP_LOCAL_TIMEZONE=Europe/Bratislava  # predvolené, netreba meniť

# NOVÉ: heslo k stránke Záznamy z minulých ročníkov (/zaznamy).
# Bez neho sa do záznamov nedá prihlásiť nikto.
RECORDINGS_PASSWORD=nove-heslo

# Skutočný SMTP server (lokálne sa používa Mailpit)
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_SCHEME=null
MAIL_FROM_ADDRESS="konferenciaAI@fri.uniza.sk"
MAIL_FROM_NAME="Konferencia o AI vo vzdelávaní"
```

Pôvodné heslo k záznamom bolo napísané priamo v kóde a je vo verejnej histórii repozitára na GitHube.
Zvoľte preto **nové heslo** a pošlite ho účastníkom.

`APP_TIMEZONE` nemeňte: databáza ostáva v UTC ako doteraz, slovenský čas sa používa pri zobrazení
a termínoch.

## 6. CSS a JavaScript administrácie

Na serveri:

```bash
npm ci
npm run build
```

Alebo bez Node.js na serveri: spustite `npm ci && npm run build` u seba a nahrajte celý adresár
`public/build` na server. Verejná časť webu používa `public/css/conference.css` a písmo
v `public/fonts`, tie sú priamo v gite.

## 7. Migrácie databázy

```bash
sudo -u www-data php artisan migrate --force
```

Spustí sa týchto 5 migrácií:

| Migrácia | Čo urobí |
|---|---|
| `2026_02_18_000000_add_record_and_presentation_to_registrations` | stĺpce už na produkcii sú, preskočí ich |
| `2026_11_15_000000_add_editions_to_conferences` | z doterajšej konferencie urobí ročník 2026 (archív), vytvorí aktívny ročník 2027 (2. 2. 2027) a priradí registrácie a otázky k ročníku 2026 |
| `2026_11_15_100000_add_contribution_deadline_to_conferences` | termín na príspevky; ročník 2026 dostane 4. 2. 2026 |
| `2026_11_15_120000_add_review_status_to_registrations` | stav posúdenia; doterajších 27 príspevkov bude schválených |
| `2026_11_15_140000_add_locale_to_registrations` | jazyk účastníka (doterajší: slovenčina) |

Kontrola: `php artisan migrate:status` nesmie ukázať nič ako `Pending`.

## 8. Cache a práva

```bash
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

Príkazy `php artisan` spúšťajte ako používateľ web servera, nie ako root. Inak vzniknú súbory
patriace rootovi a administrácia spadne s chybou `touch(): Utime failed`.
Ak sa to stane, pomôže príkaz `chown` uvedený vyššie.

## 9. Spustenie

```bash
sudo -u www-data php artisan up
```

## 10. Kontrola po nasadení

Verejný web:

- [ ] Úvodná stránka ukazuje **2. ročník** a dátum **2. 2. 2027**.
- [ ] `/en` je anglická verzia, prepínač SK/EN v hlavičke stránky funguje.
- [ ] `/zaznamy`: prihlásenie novým heslom, zoznam ročníka 2026, video aj prezentácia sa otvoria.
- [ ] Na mobile je viditeľné celé menu.

Administrácia (`/admin`):

- [ ] **Ročníky**: doplňte pre 2027 termín registrácie, termín na príspevky a notifikáciu autorov
      (zatiaľ nie sú vyplnené). Kým termín registrácie chýba, registrácia je otvorená až do dňa konferencie.
- [ ] Prepnutie na ročník 2026: registrácie, program a otázky sú viditeľné, iba na čítanie.
- [ ] Export do Excelu, prezenčná listina (filter Forma účasti: Prezenčne) a PDF programu sa stiahnu.

E-maily:

- [ ] Zaregistrujte sa na `/prihlasenie` so svojou adresou ako aktívny účastník.
      Príde potvrdenie a organizátorom oznámenie o novej registrácii.
- [ ] V administrácii príspevok schváľte alebo odmietnite (so zdôvodnením). Príde e-mail o výsledku.
- [ ] Testovaciu registráciu potom zmažte.

Logy: `tail -n 50 storage/logs/laravel.log` (nesmú tam byť nové chyby).

## 11. Bezpečnosť: kontrola starej chyby v záznamoch

Pôvodný kód umožňoval cez `/zaznamy/record/...` a `/zaznamy/presentation/...` stiahnuť aj iné súbory
zo servera vrátane `.env` (path traversal). Dnešná verzia to opravuje. Skontrolujte prístupové logy
web servera, či to niekto nevyužil:

```bash
grep -E '/zaznamy/(record|presentation)/.*(\.\.|%2[eEfF])' /var/log/apache2/access.log* /var/log/nginx/access.log* 2>/dev/null
```

Ak sa nájde podozrivý úspešný prístup (kód 200), považujte obsah `.env` za uniknutý a zmeňte:
heslo k databáze, heslo k SMTP, `APP_KEY` (`php artisan key:generate`; odhlási všetkých prihlásených)
a heslo k záznamom.

## 12. Návrat späť (ak by niečo zlyhalo)

```bash
sudo -u www-data php artisan down
mysql -u UZIVATEL -p NAZOV_DB < ~/aifri-db-DATUM.sql
git checkout $(cat ~/aifri-pred-nasadenim.commit)
cp ~/aifri-env-DATUM.bak .env
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan up
```

Pozor: pôvodná verzia potrebuje Laravel 12. Ak sa PHP medzitým aktualizovalo na 8.4, pôvodný kód
na ňom beží tiež.
