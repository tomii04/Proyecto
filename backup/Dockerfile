FROM alpine:latest

RUN apk add --no-cache mysql-client bash tzdata

# Zona horaria correcta
RUN cp /usr/share/zoneinfo/America/Montevideo /etc/localtime

# Copiar script de backup
COPY backup.sh /backup.sh
RUN chmod +x /backup.sh

# CRON cada día 2:00 AM
RUN echo "0 2 * * * /backup.sh >> /var/log/backup.log 2>&1" >> /etc/crontabs/root

CMD ["crond", "-f"]
