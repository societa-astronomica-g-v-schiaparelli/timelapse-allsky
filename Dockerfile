## Container for timelapse-allsky
# Copyright (c) 2020-2023 - Dario Pilori <dario.pilori@astrogeo.va.it>
# SPDX-License-Identifier: MIT
FROM ubuntu:22.04
LABEL maintainer="dario.pilori@astrogeo.va.it"
ENV TZ=Europe/Rome

# Install dependencies
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get -y update && apt-get -y install \
    php-cli graphicsmagick-imagemagick-compat mencoder ffmpeg \
    && rm -rf /var/lib/apt/lists/*

# Select volume for images
VOLUME /media/allsky

# Create unprivileged user
RUN useradd -r -s /sbin/nologin -m -d /home/allsky -u 1001 allsky
USER allsky

# Install script
ADD timelapse_allsky.php /home/allsky
ADD ftp_settings.php /home/allsky
ADD logo.png /home/allsky

# Run PySQM
WORKDIR /home/allsky
CMD php timelapse_allsky.php

