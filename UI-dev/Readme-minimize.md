
********************************************************************************
GENERATE APP
********************************************************************************
ext-6.2.1-premium EXAMPLE:
$ cd /home/administrador/NetBeansProjects/<project>/lib/sencha/Cmd/
$ ./6.2.2.36/sencha -sdk ../../../ext-6.2.1-premium/ generate app App /home/administrador/NetBeansProjects/<project>/UI-6.2.1-premium/

ext-6.2.0-gpl:
$ cd /home/administrador/NetBeansProjects/<project>/
$ ./lib/sencha/Cmd/6.2.2.36/sencha -sdk /home/administrador/NetBeansProjects/<project>/lib/sencha/ext-6.2.0-gpl/ generate app App /home/administrador/NetBeansProjects/<project>/UI-dev/ --classic


********************************************************************************
BUILDING APP
********************************************************************************
$ cd /home/administrador/NetBeansProjects/<project>/
$ cd UI-dev/ && ./lib/sencha/Cmd/6.2.2.36/sencha app refresh && cd ../../
$ cd UI-dev/ && ./lib/sencha/Cmd/6.2.2.36/sencha app build development && cd ../../
$ cd UI-dev/ && ./lib/sencha/Cmd/6.2.2.36/sencha --debug app build development && cd ../../ (DEBUG)

And finally..
$ cd UI-dev/ && ./lib/sencha/Cmd/6.2.2.36/sencha app build production && cd ../../
