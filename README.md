# timelapse-allsky
Set of scripts, and a Dockerfile, to clean up old AllSky images and generate a daily timelapse.

## Usage
### Build
Build with: 
```bash
buildah bud -t allsky-timelapse .
```

### Run
Assuming that the data directory is `/media/allsky`, run with:
```bash
podman run --rm --user allsky -v /media/allsky:/media/allsky -t allsky-timelapse 
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
