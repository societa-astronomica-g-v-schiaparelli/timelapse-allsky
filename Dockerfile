# Base information
FROM ubuntu:20.04
MAINTAINER Dario Pilori <dario.pilori@astrogeo.va.it>

# Update
RUN apt -y update && apt -y full-upgrade && apt -y clean

# Install dependencies
RUN apt -y install php-cli graphicsmagick-imagemagick-compat mencoder ffmpeg

# Select volume for images
VOLUME /media/allsky

# Create unprivileged user
RUN useradd -r -s /sbin/nologin -m -d /home/allsky -u 1001 allsky
USER allsky

# Install script
ADD timelapse_allsky.php /home/allsky
ADD logo.png /home/allsky

# Run PySQM
ENTRYPOINT cd /home/allsky && php timelapse_allsky.php

