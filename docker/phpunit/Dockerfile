FROM php:7.4

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip

RUN curl --silent --show-error https://getcomposer.org/installer | php

# RUN apk --no-cache add php7-iconv
# RUN apk --no-cache add php7-simplexml
