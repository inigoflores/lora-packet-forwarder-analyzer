#!/bin/bash
ngrep -W byline port 1680 -d lo | grep rxpk --line-buffered | gawk '{ print strftime("%Y-%m-%d %H:%M:%S",systime(),1), $0;system("") }'
