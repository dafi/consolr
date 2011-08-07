#!/bin/bash

# Read from file names.txt the strings to use to download from wikipedia
# Write the text dumped from HTML into directory OUT_DIR

OUT_DIR=`pwd`/results/wiki_html

mkdir -p $OUT_DIR

cat results/names.txt | while read line; do
    FILENAME=${line// /_}
    FILENAME=${FILENAME//\"/}
    PARAM=${line// /+}
    
    echo Fetching $line
    links -dump http://en.wikipedia.org/wiki/$FILENAME > $OUT_DIR/$FILENAME.txt

    #FILENAME=${line// /-}
    #links -dump http://www.celebritybirthdaylist.com/name/$FILENAME > $OUT_DIR/$FILENAME.txt
done
