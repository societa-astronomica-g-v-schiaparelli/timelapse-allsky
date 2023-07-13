# Build a timelapse video from the allsky images using ffmpeg
# The video is encoded in the VP9 format for maximum browser compatibility
# This container is meant to be run daily by a "oneshot" systemd unit

# Copyright (c) 2023 Società Astronomica G.V. Schiaparelli <dario.pilori@astrogeo.va.it>
# SPDX-License-Identifier: MIT

FROM lscr.io/linuxserver/ffmpeg:latest

# Environment parameters
ENV MAIN_DIR="/media/allsky"
ENV RES_DIR="timelapse"
ENV THREADS=4
ENV DAY=""

# Install LFTP client
RUN \
  echo "**** install lftp ****" && \
    apt-get update && \
    apt-get install -y \
    lftp && \
  echo "**** clean up ****" && \
  rm -rf \
    /var/lib/apt/lists/* \
    /var/tmp/*

# Copy script
COPY timelapse_allsky.sh /
COPY ftp_settings /

# Override entrypoint of the base container
ENTRYPOINT [ "/usr/bin/env" ]
CMD ["/bin/bash", "/timelapse_allsky.sh"]
