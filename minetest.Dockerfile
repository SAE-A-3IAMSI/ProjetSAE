FROM linuxserver/minetest:latest

COPY /ProjetSAE/Minetest/minetest-5.7.0-win64V2/mods/inv /var/lib/docker/volumes/projetsae_minetest_data/_data/mods/inv

ENV SERVER_NAME "ProjetSAE"

RUN ["./run"]
