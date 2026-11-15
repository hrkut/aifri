# Headless Chromium for screenshots of the local site (see docker/browser/README.md).
# Debian-based so fonts render like on a desktop, incl. colour emoji used across the site.
FROM node:22-bookworm-slim

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        chromium \
        fonts-noto-color-emoji \
        fonts-noto-core \
        fonts-dejavu-core \
        fonts-liberation \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

ENV PUPPETEER_SKIP_DOWNLOAD=1 \
    PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium \
    HOME=/tmp

WORKDIR /opt/browser
RUN npm init -y >/dev/null \
    && npm install --omit=dev --no-audit --no-fund puppeteer-core@23.11.1

COPY screenshot.mjs /opt/browser/screenshot.mjs

ENTRYPOINT ["node", "/opt/browser/screenshot.mjs"]
