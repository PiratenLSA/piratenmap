#!/bin/bash


curl -o lsa-gemeinden.osm -X POST -d @- http://www.overpass-api.de/api/interpreter <<DOC
<osm-script timeout="6000">
        <union>
                <query type="relation">
                        <has-kv k="de:amtlicher_gemeindeschluessel" regv="^15" />
                        <has-kv k="boundary" v="administrative" />
                        <has-kv k="admin_level" v="8" />
                </query>
                <recurse type="relation-way"/>
                <recurse type="way-node"/>
        </union>
        <print mode="meta" />
</osm-script>
DOC