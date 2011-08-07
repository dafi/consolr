#!/bin/bash
#
#SELECT * FROM `CONSOLR_BIRTHDAY` 
#where date_format(birth_date, '%m') = date_format(now(), '%m')
#order by date_format(birth_date, '%m%d')

INPUT_DIR=`pwd`/results/wiki_html
OUT_SQL=`pwd`/results/script.sql
OUT_ERR=`pwd`/results/error_conversion.sql

# http://www.famousbirthdays.com/welcome.html
# http://www.celebritybirthdaylist.com/name/aaron-b-harper
cd $INPUT_DIR

rm -f $OUT_SQL
rm -f $OUT_ERR

echo >>$OUT_SQL -e "INSERT INTO CONSOLR_BIRTHDAY (name, birth_date, tumblr_name) VALUES"

cnt=0
for i in *; do
    ((cnt++))
    tag=${i/.txt/}
    name=${tag//_/ }
    # escape quotes
    name=${name//\'/\'\'}
    date=`grep -m 1 Born $i | gawk '{if (match($0, /\(([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9])\)/, a)) {dt=a[1]}; print dt;}'`
    
    if [ "$date" == "" ];
    then
        echo >>$OUT_ERR -e "('$name', '', <<change_tumblr_name>>)," ;
    else
        echo >>$OUT_SQL -e "('$name', '$date', <<change_tumblr_name>>)," ;
    fi
done

echo >>$OUT_SQL -e ";"
