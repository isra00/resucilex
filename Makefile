build:
	docker build -t resucilexf .

#run:
#	docker run -it --rm --name resucilexf -v $PWD:/app -p 80:80 -p 2019:2019 -p 443:443 -p 443:443/udp -e SERVER_NAME=resucilex.local -e CADDY_GLOBAL_OPTIONS=debug \
#	-e FRANKENPHP_CONFIG="worker /app/public/frankenphp-worker.php" \
#	 -v ${PWD}/Caddyfile:/etc/caddy/Caddyfile --tty resucilexf

run:
	docker run -it --rm --name resucilexf -v $PWD:/app -p 80:80 -p 2019:2019 -p 443:443 -p 443:443/udp -e SERVER_NAME=resucilex.local -e CADDY_GLOBAL_OPTIONS=debug \
	 	-v ${PWD}/Caddyfile:/etc/caddy/Caddyfile --tty resucilexf