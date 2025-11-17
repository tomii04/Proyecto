#!/bin/bash

# Levantar contenedores
docker compose up -d --build

echo "Esperando MySQL..."
sleep 20

# Importar estructura y datos
docker exec -i pdc3_db mysql -uroot CooperativaS < ./app/www/Backend/bdd/"Cooperativa DDL.sql"
docker exec -i pdc3_db mysql -uroot CooperativaS < ./app/www/Backend/bdd/"Cooperativa DML.sql"

echo "Base CooperativaS inicializada sin contraseña."
