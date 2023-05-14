# timelapse-allsky
Set of scripts, and a Dockerfile, to clean up old AllSky images and generate a daily timelapse.

## General description
A SBIG (now Diffraction) [All-Sky 340](https://diffractionlimited.com/product/all-sky-340-cameras/) camera, with its original software, generates
a series images (in jpeg format) in a folder. These images can be automatically uploaded with FTP, but the software doesn't provide any
useful method to remove old images (or images taken during the day) or to generate a timelapse video.

Therefore, we've written this simple PHP script, which does exactly these things.

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
