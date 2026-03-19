FROM postgres:17-alpine

# Install other packages
RUN apk update && apk add iputils