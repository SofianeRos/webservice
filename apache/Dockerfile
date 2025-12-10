# 1. BASE IMAGE & ARGUMENTS
FROM php:8.4-apache

# 2. SYSTÈME ET DÉPENDANCES
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        wget \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libicu-dev \
        curl \
        nano \
    && rm -rf /var/lib/apt/lists/*

# 3. EXTENSIONS PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        pdo \
        pdo_mysql \
        opcache

# 4. COMPOSER
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. SYMFONY CLI (dernière version)
RUN wget https://get.symfony.com/cli/installer -O - | bash \
  && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# 6. XDEBUG
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copie de la configuration PHP personnalisée
COPY custom-php.ini /usr/local/etc/php/conf.d/

# 7. NVM et NODE.JS (dernière version)
ENV NVM_DIR=/root/.nvm

RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/master/install.sh | bash \
    && /bin/bash -c "source $NVM_DIR/nvm.sh && nvm install node && nvm alias default node && nvm use default" \
    && NODE_VERSION=$(/bin/bash -c "source $NVM_DIR/nvm.sh && node -v | sed 's/v//'") \
    && echo "export NVM_DIR=\"\$NVM_DIR\"" >> /etc/profile.d/nvm.sh \
    && echo "[ -s \"\$NVM_DIR/nvm.sh\" ] && \. \"\$NVM_DIR/nvm.sh\"" >> /etc/profile.d/nvm.sh \
    && echo "export PATH=\"\$NVM_DIR/versions/node/v\${NODE_VERSION}/bin:\$PATH\"" >> /etc/profile.d/nvm.sh \
    && echo "export NVM_DIR=\"\$NVM_DIR\"" >> /root/.bashrc \
    && echo "[ -s \"\$NVM_DIR/nvm.sh\" ] && \. \"\$NVM_DIR/nvm.sh\"" >> /root/.bashrc \
    && echo "[ -s \"\$NVM_DIR/bash_completion\" ] && \. \"\$NVM_DIR/bash_completion\"" >> /root/.bashrc \
    && ln -sf $NVM_DIR/versions/node/v${NODE_VERSION}/bin/node /usr/local/bin/node \
    && ln -sf $NVM_DIR/versions/node/v${NODE_VERSION}/bin/npm /usr/local/bin/npm \
    && ln -sf $NVM_DIR/versions/node/v${NODE_VERSION}/bin/npx /usr/local/bin/npx \
    && rm -rf /tmp/*

# 8. CONFIGURATION APACHE
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite

# 9. PERMISSIONS
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 775 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# 10. POINT D'ENTRÉE
EXPOSE 80
