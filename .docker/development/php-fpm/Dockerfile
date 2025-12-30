FROM common-php AS development

ARG XDEBUG_ENABLED=true
ARG XDEBUG_MODE=develop,debug
ARG XDEBUG_HOST=host.docker.internal

RUN if [ "$XDEBUG_ENABLED" = "true" ]; then \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo "xdebug.mode=$XDEBUG_MODE" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.client_host=$XDEBUG_HOST" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini ; \
fi

COPY ./docker/development/php-fpm/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
