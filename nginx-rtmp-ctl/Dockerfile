FROM php:7.4-cli
COPY . /app
WORKDIR /app
RUN apt-get update \
    && apt-get install -y unzip \
    && apt-get clean \
    && php composer.phar install --prefer-dist

CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
