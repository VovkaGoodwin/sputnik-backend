#!/usr/bin/env bash

case "$1" in
  serve)
    php artisan migrate --force
    rr serve -c .rr.yaml
  ;;
  *)
    echo "unknown mode [$1]"
    exit 1
  ;;
esac

exit 0
