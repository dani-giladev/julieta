#!/bin/bash
#
# Give permissions to files languages

eval "sudo chown www-data client/";
eval "sudo chown www-data server/";
eval "sudo chown www-data version.json";

eval "sudo chmod -R 775 client/";
eval "sudo chmod -R 775 server/";
eval "sudo chmod 775 version.json";
