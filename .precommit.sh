#!/bin/sh
#

for file in database/migrations/*.php ;
    do filename="${file##*/}";
    month=$(echo $filename | cut -c1-7);
    mkdir -p "database/migrations/$month"
    mv $file "database/migrations/$month"
    git add "database/migrations/*.php"
done;