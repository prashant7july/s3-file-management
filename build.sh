docker stop $(docker ps -a -q) && docker rm $(docker ps -a -q)

# docker rmi $(docker images -q)

docker build --no-cache -f ./Dockerfile -t aws-php:1.0.0 .

docker run -d -p 8080:8080 --name=aws-php aws-php:1.0.0


