# Build a timelapse video from the allsky images using ffmpeg
# The video is encoded in the VP9 format for maximum browser compatibility
# This container is meant to be run daily by a "oneshot" systemd unit

# Copyright (c) 2023 Società Astronomica G.V. Schiaparelli <dario.pilori@astrogeo.va.it>
# SPDX-License-Identifier: MIT

FROM lscr.io/linuxserver/ffmpeg:latest

# Environment parameters
ENV MAIN_DIR="/media/allsky"
ENV RES_DIR="timelapse"
ENV THREADS=10
ENV DAY=""

# Copy script
COPY timelapse_allsky.sh /

# Override entrypoint of the base container
ENTRYPOINT [ "/usr/bin/env" ]
CMD ["/bin/bash", "/timelapse_allsky.sh"]