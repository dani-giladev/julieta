Ext.define('App.core.backend.UI.controller.maintenance.type1ModalForm', {
    extend: 'App.core.backend.UI.controller.maintenance.type1',

    requires: [
        'App.core.backend.UI.view.maintenance.type1ModalForm.maintenance',
        'App.core.backend.UI.view.maintenance.type1ModalForm.form',
        'App.core.backend.UI.view.maintenance.type1ModalForm.toolBar'
    ],
    
    getWindowView: function(config)
    {
        // Find form by itemId
        var itemId = 'maintenance_type1_modalform' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var form = Ext.ComponentQuery.query('#' + itemId)[0];
        return form;
    },
            
    getFormView: function(config)
    {
        // Find form by itemId
        var itemId = 'maintenance_type1_modalform_form' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var form = Ext.ComponentQuery.query('#' + itemId)[0];
        return form;
    },
    
    showForm: function(config, is_new_record, record)
    {
        var me = this;
    
        var window = Ext.create('App.core.backend.UI.view.maintenance.type1ModalForm.maintenance', {
            config: config,
            is_new_record: is_new_record
        });
        
        var form = me.getFormView(config);
        
        // Disabled fields on edit
        me.setDisabledFieldsOnEdit(!is_new_record, form);       
        
        if (!is_new_record)
        {
            // Adding editable data to form
//            console.log(record);
            form.getForm().loadRecord(record);
        }
        else
        {
            // Clean form
            form.reset();
            //extjs6 form.clearDirty();           
        }        
        
        window.show();
        
        return window;
    },
            
    saveRecord: function(config, publish)
    {
        var me = this;
        var form = me.getFormView(config);
        
        if(form.getForm().isValid())
        {
            if (publish)
            {
                if (is_super_user)
                {
                    me.savingRecord(config, true);
                }
                else
                {
                    Ext.MessageBox.show({
                        title: me.trans('publish_record'),
                        msg: me.trans('are_you_sure_to_publish'),
                        buttons: Ext.MessageBox.YESNO,
                        icon: Ext.MessageBox.QUESTION,
                        fn: function(btn, text)
                        {
                            if(btn === 'yes')
                            {
                                me.savingRecord(config, true);
                            } 
                            else
                            {
                                me.savingRecord(config, false);
                            } 
                        }
                    });                    
                }
            }
            else
            {
                me.savingRecord(config, false);
            }
        }
    },
            
    savingRecord: function(config, publish)
    {
        var me = this;
        var form = me.getFormView(config);
        var is_new_record = form.is_new_record;
        var record = form.getRecord();
        var params, discard_fields;
        var record_id = record.data.id;
        var save_controller = 'core\\backend\\controller\\maintenance\\type1ModalForm';
        var save_method = 'saveRecord';
        
        // Check for overrided savecontroller definition
        if (config.save_controller)
        {
            save_controller = config.save_controller;
        }
        if (config.save_modal_form_method)
        {
            save_method = config.save_modal_form_method;
        }
        
        // The ajax params
        params = {
            controller: save_controller, 
            method: save_method,
            module_id: config.module_id,
            model_id: config.model.id,
            is_new_record: is_new_record,
            record_id: record_id,
            publish: publish
        };        
        // Add params with disabled fields
        params = me.addSubmitDisabledFields(params, form);        
        // Add params with checkbox fields with checked=false
        params = me.addSubmitUncheckedFields(params, form);
        // Add params with others submit values from combo fields
        params = me.addSubmitValuesFromComboFields(params, form);
        // Add params with others submit values from grid panels
        params = me.addSubmitValuesFromGridPanels(params, form);
        
        // Add fields or properties to discard
        if (config.model.discard_fields)
        {
            discard_fields = Ext.JSON.encode(config.model.discard_fields);    
            params['discard_fields'] = discard_fields;
        } 
        
        form.getForm().submit(
        {
            type: 'ajax',
            url : 'index.php',
            method: 'POST',
            params: params,
            waitMsg : me.trans('saving_record'),
            success: function(thisForm, data)
            {
                var obj = Ext.JSON.decode(data.response.responseText);
                if(obj.success)
                {
                    var new_record = obj.data.result;
                    var store = config.store;  
                    
                    var scroll_position = null;
                    var grid = me.getGrid(config);
                    if (grid)
                    {
                        scroll_position = grid.getEl().down('.x-grid-view').getScroll();
                    }
      
                    if (is_new_record)
                    {
                        store.add(new_record);
                    }
                    else
                    {
                        // Replace record
                        var index = store.indexOf(record);
                        store.removeAt(index);                        
                        store.on('add', function(this_store, records, idx, eOpts) 
                        {
                            var record = store.getAt(idx);
                            me.editRecord(config, record);
                            
                            // Select row on grid
                            if (grid)
                            {
                                //console.log(idx);
                                grid.getSelectionModel().deselectAll(true);
                                grid.getSelectionModel().select(idx); 
                            }
                        
                        }, this, {single: true});
                        store.insert(index, new_record);
                    }  
                        
                    // Set scroll
                    if (grid)
                    {
                        var task = new Ext.util.DelayedTask(function(){
                            grid.getEl().down('.x-grid-view').scrollTo('top', scroll_position.top, false);
                        });        
                        task.delay(100);
                    }
                    
                    // Close window
                    var window = me.getWindowView(config);
                    window.close();              
                }
                else
                {
                    Ext.MessageBox.show({
                       title: me.trans('saving_record_failed'),
                       msg: obj.data.result,
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.ERROR
                    });
                }
            },
            failure: function(form, data)
            {
                var obj = Ext.JSON.decode(data.response.responseText);
                Ext.MessageBox.show({
                   title: me.trans('saving_record_failed'),
                   msg: obj.data.result,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
                });
            }
        });
    },
            
    cloneConfig: function(config)
    {
        //console.log(config);
        return {
            module_id: config.module_id,
            model: config.model,
            save_controller: config.save_controller,
            save_modal_form_method: config.save_modal_form_method,
            enable_publication: config.enable_publication,
            publish_controller: config.publish_controller,
            permissions: config.permissions,
            store: config.store
        };
    }
    
});