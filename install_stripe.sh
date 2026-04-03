#!/bin/bash
cd /home/hichem/wr602d
docker compose exec web bash -c "cd /var/www && composer require stripe/stripe-php --ignore-platform-req=ext-curl"
