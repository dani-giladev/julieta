#!/bin/bash
#
# Code lines counter

total=0;

# **************************************************
# CORE
# **************************************************
path="core";
total_js=$(find $path/ -name '*.js' -o -name '*.php');
counter=0;
for i in $total_js; 
do 
    lines_file=$(wc -l $i | awk '{ print $1 }');
    let counter+=lines_file; 
done

echo "TOTAL lines in: $path = $counter";
let total=total+counter;

# **************************************************
# MODULES
# **************************************************
path="modules";
total_js=$(find $path/ -name '*.js' -o -name '*.php');
counter=0;
for i in $total_js; 
do 
    lines_file=$(wc -l $i | awk '{ print $1 }');
    let counter+=lines_file; 
done

echo "TOTAL lines in: $path = $counter";
let total=total+counter;

echo "TOTAL lines: $total"

exit 0;