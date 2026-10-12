FROM php:8.4-apache-bookworm
# syntax is supported by BuildKit; optional CA is used only during verified package downloads.
RUN --mount=type=secret,id=extra_ca,mode=0444 \
    set -eu; \
    if [ -f /run/secrets/extra_ca ]; then \
      printf 'Acquire::https::CaInfo "/run/secrets/extra_ca";\n' > /etc/apt/apt.conf.d/99-build-ca; \
    fi; \
    sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources; \
    apt-get update; \
    apt-get install -y --no-install-recommends libonig-dev python3; \
    docker-php-ext-install -j2 pdo_mysql mbstring; \
    a2enmod rewrite; \
    rm -f /etc/apt/apt.conf.d/99-build-ca; \
    rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html
RUN find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod +x docker/entrypoint.sh \
    && mkdir -p var/sessions \
    && chown -R www-data:www-data var
EXPOSE 80
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["apache2-foreground"]
