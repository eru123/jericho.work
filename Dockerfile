FROM lighty262/afs:1.0

WORKDIR /app

COPY html/composer.json html/composer.lock ./
RUN composer install -o 

COPY system/ /
COPY html .

RUN cp /app/script /usr/bin/script \
    && cp /app/check /usr/bin/check \
    && cp /app/make-migration /usr/bin/make-migration \
    && cp /app/migrate /usr/bin/migrate \
    && chmod +x /usr/bin/script \
    && chmod +x /usr/bin/check \
    && chmod +x /usr/bin/make-migration \
    && chmod +x /usr/bin/migrate

RUN chmod +x /usr/bin/skiddph \
    && chmod +x /usr/bin/skiddph-daemon \
    && chmod +x /usr/bin/skiddph-ws

RUN pnpm install --prefix client/cdn \
    # && pnpm install --prefix client/admin \
    && pnpm install --prefix client/main \
    # && cd /app/client/admin && pnpm build \
    && cd /app/client/main && pnpm build \
    && cd /app/client/cdn && pnpm build

# Production only - Delete FEs source code
RUN find /app/client -mindepth 2 -maxdepth 2 -not -name 'dist'  -exec rm -rf {} \;
RUN find /app/client -mindepth 1 -maxdepth 1 -not -name 'admin' -not -name 'cdn' -not -name 'main' -exec rm -rf {} \;

# Include port 9000 if Websocket is enabled
EXPOSE 80 
ENTRYPOINT ["/usr/bin/skiddph"]
