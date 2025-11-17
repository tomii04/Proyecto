#!/bin/bash

DATE=$(date +"%Y-%m-%d_%H-%M")
BACKUP_DIR="/backups/${DATE}"
mkdir -p "$BACKUP_DIR"

echo "=== BACKUP INICIADO ==="

# Backup MySQL
mysqldump -h pdc3_db -u root -proot cooperativa > "$BACKUP_DIR/db.sql"

# Backup archivos de la app
cp -r /app/www "$BACKUP_DIR/www"

echo "Backup terminado: $BACKUP_DIR"
