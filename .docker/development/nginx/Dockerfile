FROM nginx:alpine

# Remove default config
RUN rm /etc/nginx/conf.d/default.conf

# Copy our nginx config
COPY default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www
