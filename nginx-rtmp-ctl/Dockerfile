FROM php:7.4-cli
COPY . /app
WORKDIR /app
RUN php composer.phar install

CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
