# Tasks

- [x] Build the `github2` part in `bin/calibration/build-corpus`, sharing the code of the `github` part
- [x] Run it in `bin/calibration/run github2`, on its module paths only, and name it in `bin/calibration/report`
- [x] Build the part and record the manifest; the `github` part is not rebuilt, and its section of the manifest stays as it was, since `github` takes no earlier part and none of its modules has the name of an installed one
