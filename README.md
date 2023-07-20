# timelapse-allsky
Set of scripts, and a Dockerfile, to clean up old AllSky images, generate and upload a daily timelapse video.

This project has been archived, since the active development has been moved to a private repository.

## General description
A [Alcor-System OMEA 5C](https://www.alcor-system.com/new/AllSky/Omea_camera.html) camera, with its original software, generates
a series images (in JPEG format) in a folder. The software builds a timelapse video in an *ancient* format (AVI with [XviD](https://www.xvid.com/) codec).
This script generates a video in a modern, and browser-compatible, format: [VP9](https://developers.google.com/media/vp9) with WebM container.

## Usage
Install the latest version of [Docker Engine](https://docs.docker.com/engine/install/).

### Build
Build with: 
```bash
docker build -t allsky-timelapse .
```

### Run
Assuming that the data directory is `/media/allsky`, run with:
```bash
docker run --rm --user allsky -v /media/allsky:/media/allsky -t allsky-timelapse 
```
This command is meant to be run daily.

### Systemd timer
This command can be daily run using the systemd timer `run-timelapse-allsky.timer` and the
corresponding service `run-timelapse-allsky.service`. Edit them accordingly, then run
```bash
cp run-timelapse-allsky.* /etc/systemd/system
systemctl daemon-reload
systemctl --now enable run-timelapse-allsky.timer
```

## TODO
For the time being, the following features are not yet implemented:
- Cleaning up of old images