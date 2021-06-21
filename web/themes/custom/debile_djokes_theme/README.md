# Debile Djokes – the theme

```sh
docker run --volume ${PWD}:/app --workdir /app node:latest yarn install
docker run --volume ${PWD}:/app --workdir /app node:latest yarn build
```

```sh
docker run --interactive --tty --volume ${PWD}:/app --workdir /app node:latest yarn watch
```
