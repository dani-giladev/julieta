Ext.define('App.core.backend.UI.controller.maintenance.typeTree', {
    extend: 'App.core.backend.UI.controller.maintenance.type1',

    requires: [
        'App.core.backend.UI.view.maintenance.typeTree.maintenance',
        'App.core.backend.UI.view.maintenance.typeTree.tree',
        'App.core.backend.UI.view.maintenance.typeTree.toolBar',
        'App.core.backend.UI.view.maintenance.typeTree.editForm.editForm',
        'App.core.backend.UI.view.maintenance.typeTree.editForm.form',
        'App.core.backend.UI.view.maintenance.typeTree.editForm.toolBar'
    ],
    
    getTreeView: function(config)
    {
        // Find form by itemId
        var itemId = 'maintenance_typetree_tree' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var form = Ext.ComponentQuery.query('#' + itemId)[0];
        return form;
    },
            
    getEditFormView: function(config)
    {
        // Find form by itemId
        var itemId = 'maintenance_typetree_editform_form' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var form = Ext.ComponentQuery.query('#' + itemId)[0];
        return form;
    },
    
    refreshTree: function(config)
    {
        var tree = this.getTreeView(config);
        var tree_store = tree.getStore();
        tree_store.reload();
    },
    
    showEditForm: function(config, is_new_node)
    {
        var me = this;
        var tree = me.getTreeView(config);
        var selected = tree.getSelectionModel().getSelection();
        var selectedNode = selected[0];
        var title_msg;
        
        if (is_new_node)
        {
            title_msg = me.trans('new_branch');
        }
        else
        {
            title_msg = me.trans('edit_branch');
        }
        
        if (!selectedNode)
        {
            Ext.MessageBox.show({
               title: title_msg,
               msg: me.trans('you_should_select_branch'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }
        
        if (!is_new_node && selectedNode.data.id === 'tree-root')
        {
            Ext.MessageBox.show({
               title: title_msg,
               msg: me.trans('root_cannot_be_edited'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }   
        
        var window = Ext.widget('maintenance_typetree_editform',{
            config: config,
            is_new_node: is_new_node
        });
        
        var form = this.getEditFormView(config);
        
        // Disabled fields on edit
        me.setDisabledFieldsOnEdit(!is_new_node, form);
        
        if (!is_new_node)
        {
            // Adding editable data to form
            var record = me.getNodeRecord(selectedNode);
            form.getForm().loadRecord(record);      
            //console.log(record);
        }
        else
        {
            // Clean form
            form.reset();
            //extjs6 form.clearDirty();
            
//            var code = selectedNode.raw._data.code;
//            if (code !== 'root')
//            {
//                var field_code = form.down('[name=code]');
//                field_code.setValue(code);
//            }
            
            var code = Ext.Date.format(new Date(), 'YmdHis') + '-' + Math.floor((Math.random() * 999) + 1);
            //console.log(code);
            var field_code = form.down('[name=code]');
            field_code.setValue(code);
        }

        window.show();
    },
    
    accept: function(config, is_new_node)
    {
        var me = this;
        var form = this.getEditFormView(config);
        
        if(!form.getForm().isValid())
        {
            return false; 
        }

        if (is_new_node)
        {
            // New node
            if (!me.addNode(config))
            {
                return false;
            }
        }
        else
        {
            // Edit node
            if (!me.editNode(config))
            {
                return false;
            }                
        }
        
        return true;
    },
    
    addNode: function(config)
    {
        var me = this;
        var form = this.getEditFormView(config);
        var values = form.getForm().getValues();
        var tree = me.getTreeView(config);            
        var selected = tree.getSelectionModel().getSelection();
        var selectedNode = selected[0];
        selectedNode.expandChildren(true); // Optional: To see what happens
            
        var new_id;
        //console.log(selectedNode.data.id);
//        if (selectedNode.data.id === 'tree-root')
//        {
//            new_id = values.code;
//        }
//        else
//        {
//            new_id = selectedNode.data.id + '|' + values.code;
//        }
        new_id = values.code;
//        console.log(new_id);
        var tree_store = tree.getStore();
//        console.log(new_id);
        var exist = tree_store.getNodeById(new_id);
        //var exist = parentNode.findChild('id', new_id);            
//        console.log(exist);

        if (!Ext.isEmpty(exist))
        {
            Ext.MessageBox.show({
               title: me.trans('new_branch'),
               msg: me.trans('duplicated_branch'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.ERROR
            });
            return false;              
        }                

        var _data = {};
        var fields = form.query('[name]');
        if (fields)
        {
            Ext.each(fields, function(item) {
                _data[item.name] = item.value;
            });
        }            
        _data['id'] = new_id;
            
        var newNode = {
            id: new_id,
            text: values.name,
//            expanded: true, // Fix extjs6
            leaf: true, // Fix extjs6
            _data: _data//,
//            children: [] // Fix extjs6
        };     
        
//        console.log(newNode);
//        console.log(selectedNode);

        // Fix extjs6
//        selectedNode.appendChild(newNode);
        try {
            var n = selectedNode.createNode(newNode);
            n.raw = {};
            n.raw._data = _data;
//            n.set('leaf', false);
//            n.set('children', []);
//            n.set('expanded', true);
            selectedNode.appendChild(n);         
        } catch(err) {
            console.log(err.message);
        }        
        
        return true;
    },
    
    editNode: function(config)
    {
        var me = this;
        var form = this.getEditFormView(config);
        var values = form.getForm().getValues();
        var tree = me.getTreeView(config);             
        var selected = tree.getSelectionModel().getSelection();
        var selectedNode = selected[0];
        
        try {
            selectedNode.expandChildren(true); // Optional: To see what happens            
        } catch(err) {
            console.log(err.message);
        }
        
//        var parentNode = selectedNode.parentNode;
//        var new_id = parentNode.data.id + '|' + values.code;
        var code = form.getForm().findField('code').getValue();
        var new_id = code;
//        console.log(new_id);
        var tree_store = tree.getStore();
        var exist = tree_store.getNodeById(new_id);
        //var exist = parentNode.findChild('id', new_id);            
        //console.log(exist);

        if (selectedNode.data.id !== new_id)
        {
            if (!Ext.isEmpty(exist))
            {
                Ext.MessageBox.show({
                   title: me.trans('edit_branch'),
                   msg: me.trans('duplicated_branch'),
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
                });
                return false;               
            }                       
        }
        
        var _data = {};
        var fields = form.query('[name]');
        if (fields)
        {
            Ext.each(fields, function(item) {
                _data[item.name] = item.value;
            });
        }            
        _data['id'] = new_id;
        
        /*
        selectedNode.data.id = new_id;
        selectedNode.data.text = values.name;
        selectedNode.raw.id = new_id;
        selectedNode.raw.text = values.name;
        selectedNode.raw._data = _data;
        try {
            selectedNode.collapse(false, function(){
                selectedNode.expand();
            });       
        } catch(err) {
            console.log(err.message);
        }*/
        
        selectedNode.set('id', new_id);
        selectedNode.set('text', values.name);
        selectedNode.set('_data', _data);

        return true;
    },
    
    deleteNode: function(config)
    {
        var me = this;
        var tree = me.getTreeView(config);                
        var selected = tree.getSelectionModel().getSelection();
        var selectedNode = selected[0];
        
        if (!selectedNode)
        {
            Ext.MessageBox.show({
               title: me.trans('delete_branch'),
               msg: me.trans('you_should_select_branch'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }
        
        if (selectedNode.data.id === 'tree-root')
        {
            Ext.MessageBox.show({
               title: me.trans('delete_branch'),
               msg: me.trans('root_cannot_be_deleted'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }   
        
        var parentNode = selectedNode.parentNode;
        parentNode.removeChild(selectedNode);
    },
    
    getNodeRecord: function(node)
    {
        var form_fields = [];
        var raw_data = node.raw._data;
        for (var item_key in raw_data) {
            form_fields.push({name: item_key});
        }   
        var form_store = Ext.create('Ext.data.Store', {
            fields : form_fields
        });
        form_store.add(raw_data);
        return form_store.getAt(0); 
    },  
    
    getNodeData: function(node, fields) 
    {
        var me = this;
        var data = {};

        // loop through desired fields
        Ext.each(fields, function(fieldName) {
            if (fieldName === 'children')
            {
                //console.log(node.childNodes);
                var children = data.children = [];
                if (node.hasChildNodes() && !Ext.isEmpty(node.childNodes)) {
                    node.eachChild(function(child) {
                        children.push(me.getNodeData(child, fields));
                    });
                }                
            }
            else
            { 
                var value = node.get(fieldName);
                if (Ext.isEmpty(value))
                {
                    data[fieldName] = node.raw[fieldName];
                }
                else
                {
                    data[fieldName] = value;
                }
            }            
        });

        return data;
    },
            
    save: function(config, publish)
    {
        var me = this;
        
        if (publish)
        {
            Ext.MessageBox.show({
                title: me.trans('publish_tree'),
                msg: me.trans('are_you_sure_to_publish_tree'),
                buttons: Ext.MessageBox.YESNO,
                icon: Ext.MessageBox.QUESTION,
                fn: function(btn, text)
                {
                    if(btn === 'yes')
                    {
                        me.saving(config, true);
                    } 
                    else
                    {
                        me.saving(config, false);
                    } 
                }
            });
        }
        else
        {
            me.saving(config, false);
        }
    },
            
    saving: function(config, publish)
    {
        var me = this;
        var tree = me.getTreeView(config);                
        //var root_node = tree.getRootNode();
        var tree_store = tree.getStore();
        var root_node = tree_store.getNodeById('tree-root');
        //console.log(root_node);   
        
        var serialized_tree;
        //serialized_tree = root_node.serialize();
        //console.log(serialized_tree);
        var fields = ['id', 'expanded', 'parentId', 'text', 'leaf', '_data', 'children'];
        serialized_tree = me.getNodeData(root_node, fields);
//        console.log(serialized_tree); 

        // Check for overrided savecontroller definition
        var save_controller = 'core\\backend\\controller\\maintenance\\typeTree';
        var save_method = 'saveTree';
        if (config.save_controller)
        {
            save_controller = config.save_controller;
        }
        if (config.save_method)
        {
            save_method = config.save_method;
        }
        
        // The ajax params
        var params = {
            controller: save_controller, 
            method: save_method,
            module_id: config.module_id,
            model_id: config.model.id,
            publish: publish,
            tree: Ext.JSON.encode(serialized_tree)
        };
        
        Ext.getBody().mask(me.trans('saving_tree'));
        
        Ext.Ajax.request({
            type: 'ajax',
            url : 'index.php',
            method: 'POST',
            params: params,
            //waitMsg : me.trans('saving_tree'),
            success: function(response, opts)
            {
                Ext.getBody().unmask();
                var obj = Ext.JSON.decode(response.responseText);
                if(!obj.success)
                {
                    Ext.MessageBox.show({
                       title: me.trans('saving_tree_failed'),
                       msg: obj.data.result,
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.ERROR
                    });
                }
                
                // Reload tree
                tree_store.reload();
            },
            failure: function(form, data)
            {
                Ext.getBody().unmask();
                var obj = Ext.JSON.decode(data.response.responseText);
                Ext.MessageBox.show({
                   title: me.trans('saving_tree_failed'),
                   msg: obj.data.result,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
                });
            }
        });
    },
    
    publish: function(config)
    {
        var me = this;
        var tree = me.getTreeView(config);                
        var tree_store = tree.getStore();
        var publish_controller = 'core\\backend\\controller\\maintenance\\typeTree';
            
        Ext.MessageBox.show({
            title: me.trans('publish_tree'),
            msg: me.trans('are_you_sure_to_publish_tree'),
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION,
            fn: function(btn, text)
            {
                if(btn === 'yes')
                {
                    // Check for overrided publish controller
                    if (config.publish_controller)
                    {
                        publish_controller = config.publish_controller;
                    }
                    
                    // The ajax params
                    var params = {
                        controller: publish_controller, 
                        method: 'publishTree',
                        module_id: config.module_id,
                        model_id: config.model.id
                    };
        
                    Ext.getBody().mask(me.trans('publishing_tree'));

                    Ext.Ajax.request({
                        type: 'ajax',
                        url : 'index.php',
                        method: 'GET',
                        params: params,
                        //waitMsg : me.trans('publishing_tree'),
                        success: function(response, opts)
                        {
                            Ext.getBody().unmask();
                            var obj = Ext.JSON.decode(response.responseText);
                            if(!obj.success)
                            {
                                Ext.MessageBox.show({
                                   title: me.trans('publishing_tree_failed'),
                                   msg: obj.data.result,
                                   buttons: Ext.MessageBox.OK,
                                   icon: Ext.MessageBox.ERROR
                                });
                            }

                            // Reload tree
                            tree_store.reload();
                        },
                        failure: function(form, data)
                        {
                            Ext.getBody().unmask();
                            var obj = Ext.JSON.decode(data.response.responseText);
                            Ext.MessageBox.show({
                               title: me.trans('publishing_tree_failed'),
                               msg: obj.data.result,
                               buttons: Ext.MessageBox.OK,
                               icon: Ext.MessageBox.ERROR
                            });
                        }
                    });                    

                }
            }
        });
        
        return true;
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    }
});