
# DM_Tool
Making a tool that will be solving single point of failure for producer.
-Run: docker-compose up
-
-Test using: docker exec -it DM_Tool php /home/app/src/Commands/Core.php EmailTask.produce --message=hello
-
-Open mailhog: localhost:8025, http://localhost:15672/#/queues
-
