# Prehliadač na snímky obrazovky

Headless Chromium (Puppeteer) v Dockeri na kontrolu vzhľadu lokálneho webu.
Služba `browser` je v `compose.yaml` v profile `tools`, takže ju `up -d` nespúšťa.

```bash
# úvodná stránka a prihlásenie, desktop aj mobil, celá dĺžka stránky
docker compose -p aifri run --rm browser --width=1280,390 --full / prihlasenie

# administrácia (najprv sa prihlási)
docker compose -p aifri run --rm browser --login=admin@example.com:heslo admin/rocniky

# záznamy (heslo z RECORDINGS_PASSWORD)
docker compose -p aifri run --rm browser --recordings=heslo zaznamy/list

# maily v Mailpite
docker compose -p aifri run --rm browser http://mailpit:8025
```

Snímky sa ukladajú do `.screenshots/` (mimo gitu). Ďalšie voľby sú v hlavičke
`screenshot.mjs`: `--height`, `--name`, `--motion` (predvolene sa animácie vypínajú
cez `prefers-reduced-motion`, aby boli snímky ostré).

## WSL2 / Docker Desktop

- Prehliadač beží v sieti `sail` a web otvára cez `http://laravel.test` (alias
  aplikácie), nie cez `localhost` a nie cez `network_mode: host`, ktorý sa na
  Docker Desktop správa inak ako na natívnom Linuxe.
- `shm_size: 1gb` a `--disable-dev-shm-usage`: Chromium inak v kontajneri padá.
- `--disable-gpu`: vo VM WSL2 nie je GPU.
- Súbory zapisuje pod `WWWUSER`/`WWWGROUP` (UID 1000), nie ako root.
- Priečinok `.screenshots/` je v repozitári (s vlastným `.gitignore`); keby chýbal,
  Docker Desktop by ho vytvoril ako root a zápis by zlyhal.
