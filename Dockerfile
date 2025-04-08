FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    curl \
    unzip

WORKDIR /app
COPY . /app

CMD ["php", "bot.php"]
