#!/bin/bash

cd UI-dev/
./../lib/sencha/Cmd/6.2.2.36/sencha app refresh
./../lib/sencha/Cmd/6.2.2.36/sencha app build production

cd ..
ln -s ../../../../UI-dev/res UI-dev/build/production/App/res