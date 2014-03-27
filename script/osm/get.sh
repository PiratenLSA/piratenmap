#!/bin/bash


function curl_gemeinden {
curl -o lsa-gemeinden.osm -X POST -d @- http://www.overpass-api.de/api/interpreter <<QUERY
<osm-script timeout="6000">
        <union>
                <query type="relation">
                        <has-kv k="de:amtlicher_gemeindeschluessel" regv="^15[0-9]{6}$" />
                        <has-kv k="boundary" v="administrative" />
                        <has-kv k="admin_level" regv="[6-8]" />
                </query>
                <recurse type="relation-way"/>
                <recurse type="way-node"/>
        </union>
        <print mode="meta" />
</osm-script>
QUERY

}

function convert_poly {
  php osm-to-poly.php lsa-gemeinden.osm

}

PATH=.:$PATH
#curl_gemeinden
convert_poly