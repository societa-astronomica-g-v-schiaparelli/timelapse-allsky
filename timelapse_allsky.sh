#!/bin/bash
# run_timelapse.sh - build a timelapse video from the allsky images using ffmpeg

# Copyright (c) 2023 Società Astronomica G.V. Schiaparelli <dario.pilori@astrogeo.va.it>
# SPDX-License-Identifier: MIT

# Read date from environment; if not given, take today
if [ -z "$DAY" ]; then
    DAY=$(date --date="yesterday" +%Y-%m-%d)
fi

## Import FTP settings from `ftp_settings'
#FTPHOST="ftp.example.com"
#FTPUSER="username"
#FTPPASS="password"
source ftp_settings

# Execute timelapse with the given settings
./ffmpegwrapper.sh -f image2 -ts_from_file 1 \
        -pattern_type glob -i "${MAIN_DIR}/${DAY}/*.jpg" \
        -c:v libvpx-vp9 -pix_fmt yuv420p -crf 32 \
        -b:v 1500k -minrate 900k -maxrate 2200k \
        -threads ${THREADS} -vf "scale=1434x1440,settb=1/1000,setpts=(PTS-STARTPTS)/1440,fps=25" -y -an \
        ${MAIN_DIR}/${RES_DIR}/${DAY}.webm

# Upload with FTP the result
lftp -u ${FTPUSER},${FTPPASS} ${FTPHOST} << EOF

lcd ${DIR}
put ${MAIN_DIR}/${RES_DIR}/${DAY}.webm -o allsky.webm
bye

EOF

# TODO: remove old images
