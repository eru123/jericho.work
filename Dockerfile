FROM lighty262/afs:1.0

WORKDIR /app

COPY html/composer.json html/composer.lock ./
RUN composer install -o 

COPY rootfs/ /
COPY html .
COPY client /client

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

RUN cd /client && pnpm install && pnpm build

# Include port 9000 if Websocket is enabled
EXPOSE 80 
ENTRYPOINT ["/usr/bin/skiddph"]
