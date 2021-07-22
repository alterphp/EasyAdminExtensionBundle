FROM php:7.1

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip

RUN curl --silent --show-error https://getcomposer.org/installer | php

RUN docker-php-ext-install -j$(nproc) mysqli

# Install MONGO
RUN pecl install mongodb \
	&& docker-php-ext-enable mongodb

# RUN apk --no-cache add php7-iconv
# RUN apk --no-cache add php7-simplexml
