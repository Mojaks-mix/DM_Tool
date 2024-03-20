
# DM_Tool
Making a tool that will be solving single point of failure for producer.
-run: docker-compose up
-
-test using: docker exec -it DM_Tool php /home/app/src/Commands/Core.php EmailTask.produce --message=hello
-
-open mailhog: localhost:8025, http://localhost:15672/#/queues
-
