#!/bin/bash


function loadweb {
  total=$(wc -l<liste.csv)
  let total--
  let count=1
  tail -n +2 liste.csv | while IFS=';' read key name
  do
    echo $count/$total $name
	wget -nv -N -S -O detail-$key.htm http://www.statistik.sachsen-anhalt.de/gk/statistik/gem/s/g.$key.chart.html
    let count++
  done
}

function loadgifs {
  for nr in {66..74}
  do
    wget -nv -O wk$(echo $nr).gif http://www.statistik.sachsen-anhalt.de/gk/navigation/bw/gem/_w0$(echo $nr)k.gif
  done
}


#loadgifs
#loadweb
