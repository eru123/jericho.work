sudo docker stop dev
sudo docker rm dev
sudo docker build -t dev:1 .
sudo docker run -d -p 8080:80 -p 3000:3000 --name dev \
    -v /var/www/jericho.work/html:/app \
    dev:1 

# enter container
# sudo docker exec -it dev sh

# logs container
sudo docker logs -f dev