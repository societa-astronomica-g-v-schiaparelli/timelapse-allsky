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
podman run -v /media/allsky:/media/allsky -t timelapse-allsky 
```
This command is meant to be run daily, e.g. using a systemd timer.

## Todo
Set up a systemd timer to periodically run this command.
