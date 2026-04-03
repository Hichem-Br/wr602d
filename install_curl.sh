#!/bin/bash
cd /home/hichem/wr602d
docker compose exec web bash -c "wget -O /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg && apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y php8.2-curl php-curl && service apache2 reload"
