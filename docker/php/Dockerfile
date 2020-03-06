# ./docker/php/Dockerfile
FROM php:7.2-fpm
# FROM thecodingmachine/php:7.4-v3-fpm-node10

# WORKDIR /p
# RUN mkdir -p /opt
# ADD latest.tar.gz /opt/
# RUN mv /opt/dist /opt/yarn
# ENV PATH "$PATH:/opt/yarn/bin"

# # Install packages using Yarn
# ADD package.json /tmp/package.json
# RUN cd /tmp && yarn
# RUN mkdir -p /opt/app && cd /opt/app && ln -s /tmp/node_modules

RUN docker-php-ext-install pdo_mysql

RUN pecl install apcu

RUN apt-get update && \
apt-get install -y \
zlib1g-dev

RUN apt-get update && \
    apt-get install -y gnupg2

RUN docker-php-ext-install zip
RUN docker-php-ext-enable apcu

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && mv composer /usr/local/bin/composer

RUN curl -sL https://deb.nodesource.com/setup_10.x | bash - \
    && apt-get install -y nodejs \
    && apt-get install -y build-essential


# RUN apt-get update && \
#     apt-get install -y nodejs

# RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
#     echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
#     apt-get update && \
#     apt-get install -y yarn

RUN npm install -g yarn

RUN mkdir /usr/src/app
    
WORKDIR /usr/src/app

# COPY apps/my-symfony-app /usr/src/app

# RUN mkdir /usr/src/app

# RUN composer install

# RUN yarn install

# CMD ["yarn", "encore", "dev", "--watch"]

RUN PATH=$PATH:/usr/src/apps/vendor/bin:bin
