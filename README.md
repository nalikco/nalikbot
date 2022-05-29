    docker ps -a

Get NalikBOT Container ID and add to crontab:

    * * * * * docker exec <CONTAINER_ID> php /app/cron.php

