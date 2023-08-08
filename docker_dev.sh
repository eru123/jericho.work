sudo docker stop dev
sudo docker rm dev
sudo docker build -t dev:1 .
sudo docker run -d -p 8080:80 --name dev dev:1

# enter container
sudo docker exec -it dev sh