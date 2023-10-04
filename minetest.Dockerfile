FROM alpine:latest as mods

COPY /Minetest/minetest-5.7.0-win64V2/mods/ /mods/

FROM linuxserver/minetest

COPY --from=mods /mods/ /config/.minetest/mods/