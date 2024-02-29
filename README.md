
# DM_Tool
Making a tool that will be solving single point of failure for producer.
-
1-run docker commands in Containers.txt
-
2-add consumer.conf to /etc/supervisor/conf.d, and add the path for the repo to the file.
-
3-run these commands:
-
  - sudo supervisorctl reread
  - sudo supervisorctl update
  - sudo service supervisor restart
  - sudo supervisorctl status
  
4-add corn: 0 15 * * * php repo-path/src/Commands/MyCommand EmailTask consume
-
5-open mailhog: localhost:8025
-
