Ext.define('App.core.backend.UI.controller.fileManager', {
    extend: 'App.core.backend.UI.controller.common',

    requires: [
        'App.core.backend.UI.view.fileManager.fileManager',
        'App.core.backend.UI.view.fileManager.toolbar',
        'App.core.backend.UI.view.fileManager.grid',
        'App.core.backend.UI.view.fileManager.gridToolbar',
        'App.core.backend.UI.view.fileManager.tree',
        'App.core.backend.UI.view.fileManager.treeToolbar',
        'App.core.backend.UI.view.fileManager.multiImagesGrid',
        'App.core.backend.UI.view.fileManager.multiImagesGridToolbar',
        
        'App.core.backend.UI.store.fileManager'
    ],
    
    newFolder: function(config)
    {
        var me = this;
        
        Ext.MessageBox.prompt('New folder', 'New folder name:', createDir);
        
        function createDir(button, text)
        {
            var theTree =  me.getComponentQuery('filemanager_tree', config);            
            var selected = theTree.getSelectionModel().getSelection();
            var base_node = '';
            if(selected[0])
            {
                base_node = selected[0].get('id');
            }
        
            if (base_node === 'root')
            {
                base_node = config.baseNode;
            }
            
            Ext.Ajax.request({
                url: 'index.php',
                method: 'GET',
                params:
                {
                    controller: 'core\\backend\\controller\\fileManager',
                    method: 'newFolder',
                    base_node: base_node,
                    dir_name: text
                },
                success: function(result, request)
                {
                    var obj = Ext.JSON.decode(result.responseText);
                    if(obj.success)
                    {
                        me.refresh(config);
                    }
                    else
                    {
                        Ext.MessageBox.show({
                            title: 'New folder',
                            msg: 'Unable to create the requested folder. Contact support crew if you think you should be able to perform this action.' + obj.data.result,
                            buttons: Ext.MessageBox.OK,
                            icon: Ext.MessageBox.ERROR
                        });
                    }
                },
                failure: function(response)
                {
                    Ext.MessageBox.show({
                        title: 'New folder',
                        msg: 'Unable to create the requested folder. Contact support crew if you think you should be able to perform this action.',
                        buttons: Ext.MessageBox.OK,
                        icon: Ext.MessageBox.ERROR
                    });
                }
            });
        }
    },
    
    deleteFolder: function(config)
    {
        var me = this;
            
        var theTree =  me.getComponentQuery('filemanager_tree', config);
        var selected = theTree.getSelectionModel().getSelection();
        if (!selected[0])
        {
            return;
        }
        
        var node = selected[0].get('id');
        
        Ext.MessageBox.show({
            title: 'Delete folder',
            msg: 'Are you sure to delete this folder?',
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.WARNING,
            fn: function(btn, text)
            {
                if(btn == 'yes')
                {
                    Ext.Ajax.request({
                        url: 'index.php',
                        method: 'GET',
                        params:
                        {
                            controller: 'core\\backend\\controller\\fileManager',
                            method: 'deleteFolder',
                            node: node
                        },
                        success: function(result, request)
                        {
                            var obj = Ext.JSON.decode(result.responseText);
                            if(obj.success)
                            {
                                me.refresh(config, true);
                            }
                            else
                            {
                                Ext.MessageBox.show({
                                    title: 'Delete folder',
                                    msg: 'Unable to remove the requested folder. Contact support crew if you think you should be able to perform this action.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });
                            }
                        },
                        failure: function(response)
                        {
                            Ext.MessageBox.show({
                                title: 'Delete folder',
                                msg: 'Unable to remove the requested folder. Contact support crew if you think you should be able to perform this action.',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
                    });
                }
            }
        });
    },
    
    deleteFile: function(config)
    {
        var me = this;
        
        var theGrid = me.getComponentQuery('grid', config);
        var selected = theGrid.getSelectionModel().getSelection();
        if (!selected[0])
        {
            return;
        }
        
        var filename_id = selected[0].get('filename');
        var theTree =  me.getComponentQuery('filemanager_tree', config);
        var selectedDir = theTree.getSelectionModel().getSelection();

        Ext.MessageBox.show({
            title: 'Delete file',
            msg: 'Are you sure to delete this file?',
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.WARNING,
            fn: function(btn, text)
            {
                if(btn == 'yes')
                {
                    Ext.Ajax.request({
                        url: 'index.php',
                        method: 'GET',
                        params:
                        {
                            controller: 'core\\backend\\controller\\fileManager',
                            method: 'deleteFile',
                            base_node: config.baseNode,
                            file: selectedDir[0].get('id') + '/' + filename_id
                        },
                        success: function(result, request)
                        {
                            var obj = Ext.JSON.decode(result.responseText);
                            if(obj.success)
                            {
                                me.loadGridStore(config, selectedDir[0].get('id'));
                            }
                            else
                            {
                                Ext.MessageBox.show({
                                    title: 'Delete file',
                                    msg: 'Unable to remove the requested file. Contact support crew if you think you should be able to perform this action.',
                                    buttons: Ext.MessageBox.OK,
                                    icon: Ext.MessageBox.ERROR
                                });
                            }
                        },
                        failure: function(response)
                        {
                            Ext.MessageBox.show({
                                title: 'Delete file',
                                msg: 'Unable to remove the requested file. Contact support crew if you think you should be able to perform this action.',
                                buttons: Ext.MessageBox.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
                    });
                }
            }
        });
    },
    
    /*uploadFiles: function(config)
    {
        var theTree = Ext.ComponentQuery.query('#filemanager_tree')[0];
        var selectedDir = theTree.getSelectionModel().getSelection();
        var sDir = app_base_path + '/' + filemanager_path;

        if (selectedDir[0])
        {
            var selectedDirId = selectedDir[0].get('id');
            if (selectedDirId === 'root')
            {
                if (!Ext.isEmpty(config.baseNode))
                {
                    sDir += "/" + config.baseNode;
                }
            }
            else
            {
                sDir += "/" + selectedDirId;
            }           
        }

        var centerWidth = Math.floor((window.innerWidth - 500) / 2);
        var centerHeight = Math.floor((window.innerHeight - 300) / 2);
        var path = '/res/uploader/index.php' + 
                   '?dir=' + sDir + 
                   '&app_title=' + app_title;

        var upload_window = window.open(path, 
                                        app_title,
                                        'left=' + centerWidth + ',top=' + centerHeight + 
                                        ',width=828,height=419,resizable=no,toolbar=no,location=no,status=no,directories=no,menubar=no,copyhistory=no'); 
            
        if(upload_window)
        {
            upload_window.focus();
        }

    },*/
    
    uploadFiles: function(config, form)
    {
        var me = this;
        var theTree =  me.getComponentQuery('filemanager_tree', config);
        var selected = theTree.getSelectionModel().getSelection();
        
        if (!selected[0])
        {
            return;
        }

        //var dir_id = selected[0].get('id');
        
        var selectedDir = theTree.getSelectionModel().getSelection();
        if (!selectedDir[0])
        {
            return;
        }
        var dir_id = app_base_path + '/' + filemanager_path;
        var selectedDirId = selectedDir[0].get('id');
        if (selectedDirId === 'root')
        {
            if (!Ext.isEmpty(config.baseNode))
            {
                dir_id += "/" + config.baseNode;
            }
        }
        else
        {
            dir_id += "/" + selectedDirId;
        }
        
        form.submit({
            url : 'index.php',
            method: 'GET',
            waitMsg: 'Uploading file' + '...',
            params: {
                controller: 'core\\backend\\controller\\fileManager', 
                method: 'uploadFile',
                dir_id: dir_id//,
                //maxFileSize: config.maxFileSize
            },
            success: function(fp, result) {
                me.refresh(config);
            },
            failure: function(fp, result) {
                var obj = Ext.JSON.decode(result.response.responseText);
                Ext.MessageBox.show({
                    title: 'Error uploading file',
                    msg: 'Unable to upload the requested file.</br></br>Error: ' + obj.data.result,
                    buttons: Ext.MessageBox.OK,
                    icon: Ext.MessageBox.ERROR
                });
            }
        }); 

    },
    
    visualize: function(config)
    {
        var me = this;
        var theTree =  me.getComponentQuery('filemanager_tree', config);
        var selected_dir = theTree.getSelectionModel().getSelection();
        if(!selected_dir[0])
        {
            return;
        }
        var dir_record = selected_dir[0];
        var dir_id = dir_record.get('id');

        var thisGrid = me.getComponentQuery('grid', config);
        var selected_file = thisGrid.getSelectionModel().getSelection();
        if (!selected_file[0])
        {
            return;
        }
        var image_record = selected_file[0];
        
        var filename = image_record.get('filename').toLowerCase();
        if (filename.indexOf(".pdf") !== -1)
        {
            me.showPDF(config, dir_id, image_record);
        }
        else
        {
            me.showImage(image_record);
        }
    },
    
    showPDF: function(config, dir_id, image_record)
    {
        if (dir_id === 'root' && !Ext.isEmpty(config.baseNode))
        {
            dir_id = config.baseNode;;
        }        
        var filename = image_record.get('filename');
        var path = dir_id + '/' + filename;
        
        var action = 
                '/?controller=core\\backend\\controller\\fileManager' + 
                '&method=showPDF';
                            
        var form = document.createElement("form");
        form.setAttribute("method", "post");
        form.setAttribute("action", action);
        form.setAttribute("target", "view");

        var path_field = document.createElement("input"); 
        path_field.setAttribute("type", "hidden");
        path_field.setAttribute("name", "path");
        path_field.setAttribute("value", path);
        form.appendChild(path_field);

        document.body.appendChild(form);
        window.open('', 'view');
        form.submit();               
    },
    
    showImage: function(image_record)
    {
        var relative_path = image_record.get('relativePath');
        var filename = image_record.get('filename');
        var src = '/' + filemanager_path + '/' + relative_path + '/' + filename;
        
        var window = Ext.create('Ext.window.Window', {
            title: filename,
            width: 500,
            height: 500,    
            layout: 'fit',
            modal: true        
        });
        
        window.add({
            xtype: 'image',
            src: src//,
//            height: '100%',
//            width: '100%'       
        });
            
        window.show();
    },
    
    download: function(config)
    {
        var me = this;
        
        var theTree =  me.getComponentQuery('filemanager_tree', config);
        var selected_dir = theTree.getSelectionModel().getSelection();
        if(!selected_dir[0])
        {
            return;
        }
        var dir_record = selected_dir[0];
        var dir_id = dir_record.get('id');
        if (dir_id === 'root' && !Ext.isEmpty(config.baseNode))
        {
            dir_id = config.baseNode;;
        }

        var thisGrid = me.getComponentQuery('grid', config);
        var selected_file = thisGrid.getSelectionModel().getSelection();
        if (!selected_file[0])
        {
            return;
        }
        var image_record = selected_file[0];
        
        var filename = image_record.get('filename');
        var path = dir_id + '/' + filename;
        
        var action = 
                '/?controller=core\\backend\\controller\\fileManager' + 
                '&method=downloadFile';
        
        var form = document.createElement("form");
        form.setAttribute("method", "post");
        form.setAttribute("action", action);
        form.setAttribute("target", "view");

        var path_field = document.createElement("input"); 
        path_field.setAttribute("type", "hidden");
        path_field.setAttribute("name", "path");
        path_field.setAttribute("value", path);
        form.appendChild(path_field);

        document.body.appendChild(form);
        //window.open('', 'view');
        form.submit();
    },
           
    refresh: function(config, delete_folder)
    {
        var me = this;
        var theTree =  me.getComponentQuery('filemanager_tree', config);     
        var selectedDir = theTree.getSelectionModel().getSelection();
        var selectedDirId = null;
       
        if(selectedDir[0])
        {
            selectedDirId = selectedDir[0].get('id');
            if (delete_folder)
            {
                var parentNode = selectedDir[0].parentNode;
                selectedDirId = parentNode.data.id;
            }
        }
        
        if (!Ext.isEmpty(selectedDirId))
        {
            var tree_store = theTree.getStore();
            tree_store.on('load', function(this_store, records, successful, eOpts)
            {
                var node = this_store.getNodeById(selectedDirId);
                theTree.expandNode(node);
                theTree.getSelectionModel().select(node);
                me.loadGridStore(config, selectedDirId);
            }, this, {single: true}); 
        }
        else
        {
            var node = theTree.getRootNode();
            node.expandChildren(true);
            theTree.getSelectionModel().select(node);
        }

        theTree.getStore().reload();       
    },
    
    loadGridStore: function(config, dir_id)
    {
        var me = this;
        var theGrid = me.getComponentQuery('grid', config);
        theGrid.getStore().load({
            params: {
                base_node: config.baseNode,
                dir: dir_id
            }
        });        
    },
    
    selectFile: function(config)
    {
        var me = this;
        var file_manager = me.getComponentQuery('', config);
        var thisGrid = me.getComponentQuery('grid', config);
        var selected_image = thisGrid.getSelectionModel().getSelection()[0];
        
        var path = selected_image.get('relativePath');
        if (!Ext.isEmpty(path))
        {
            path += '/';
        }
        path += selected_image.get('filename');
        
        // Fire selected file event
        file_manager.fireEvent('selectedFile', 
                                selected_image.get('filename'), 
                                selected_image.get('filesize'), 
                                selected_image.get('filedate'), 
                                selected_image.get('relativePath'),
                                path);  
    },
    
    deleteAssignedImageFromMultiImageGrid: function(config)
    {
        var me = this;
        var thisGrid = me.getComponentQuery('filemanager_multi_images_grid', config);
        var selected_image = thisGrid.getSelectionModel().getSelection()[0];
        if (!Ext.isEmpty(selected_image))
        {
            thisGrid.getStore().remove(selected_image); 
        }        
    },
    
    applyAssignedImagesFromMultiImageGridAndClose: function(config)
    {
        var me = this;
        var file_manager = me.getComponentQuery('', config);
        var thisGrid = me.getComponentQuery('filemanager_multi_images_grid', config);
        var imagesStore = thisGrid.getStore();
        
        // Fire event
        file_manager.fireEvent('applyAssignedImagesFromMultiImage', 
                                imagesStore);
    },
    
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    }
});