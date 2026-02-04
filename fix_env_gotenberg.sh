#!/bin/bash
docker exec -i symfony-web-v2 bash -c "echo 'GOTENBERG_URL=http://gotenberg:3000' >> /var/www/.env"
