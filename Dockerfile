FROM php:8.4-cli

RUN apt-get update && apt-get install -y git unzip ca-certificates libzip-dev libpng-dev \
    && docker-php-ext-install pdo_mysql zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22 /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22 /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm ci && npm run build

# Migra al arrancar. Con SEED_DEMO=true carga además los datos ficticios;
# si ya existen, el seeder falla en el primer registro y la app arranca igual.
CMD php artisan migrate --force \
    && { [ "$SEED_DEMO" != "true" ] || php artisan db:seed --force || echo "Seeder omitido: revisa el error de arriba"; } \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
