#!/bin/bash
#
#SELECT * FROM `CONSOLR_BIRTHDAY` 
#where date_format(birth_date, '%m%d') = date_format(now(), '%m%d')
#order by birth_date

INPUT_DIR=`pwd`/wiki_html

# http://www.famousbirthdays.com/welcome.html
# http://www.celebritybirthdaylist.com/name/aaron-b-harper
cd $INPUT_DIR
for i in *; do
#break;
    #echo $i \"`awk '/\(born/ {if (match($0, /\(born (.*)\)/, a)) print a[1];}' $i`\"
    echo -e ${i/.txt/} '\t' `grep -m 1 Born $i | gawk '{if (match($0, /\(([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9])\)/, a)) {dt=a[1]} else {dt=gensub(/Born/, "", $0)}; print dt;}'`
done

#grep Born Tori_Praver.txt
