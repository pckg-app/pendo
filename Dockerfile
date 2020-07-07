FROM schtr4jh/pckg:apache-alpine

COPY . /var/www/html

ENTRYPOINT ["sh", "/docker-entrypoint-apache.sh"]