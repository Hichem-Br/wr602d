#!/bin/bash
cd /home/hichem/wr602d
docker compose exec web bash -c "cd /var/www && php bin/console doctrine:query:sql \"UPDATE plan SET limit_generation = 2 WHERE name = 'Free'\""
docker compose exec web bash -c "cd /var/www && php bin/console doctrine:query:sql \"UPDATE plan SET limit_generation = 5 WHERE name = 'Premium'\""
docker compose exec web bash -c "cd /var/www && php bin/console doctrine:query:sql \"UPDATE plan SET limit_generation = 50 WHERE name = 'Enterprise'\""
