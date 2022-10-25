Ext.define('App.core.backend.UI.controller.maintenance.type1', {
    extend: 'App.core.backend.UI.controller.common',

    requires: [
        'App.core.backend.UI.view.maintenance.type1.maintenance',
        
        'App.core.backend.UI.view.maintenance.type1.gridPanel',
        'App.core.backend.UI.view.maintenance.type1.grid',
        'App.core.backend.UI.view.maintenance.type1.gridToolBar',
        
        'App.core.backend.UI.view.maintenance.type1.formPanel',
        'App.core.backend.UI.view.maintenance.type1.form',
        'App.core.backend.UI.view.maintenance.type1.formToolBar',
        
        'App.core.backend.UI.view.maintenance.type1.filterForm'
    ],
    
    getMaintenanceView: function(config)
    {
        // Find view by itemId
        var itemId = 'maintenance_type1' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var view = Ext.ComponentQuery.query('#' + itemId)[0];
        return view;
    },
            
    getForm: function(config)
    {
        // Find form by itemId
        var itemId = 'maintenance_type1_form' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var form = Ext.ComponentQuery.query('#' + itemId)[0];
        return form;
    },
            
    getFilterForm: function(config)
    {
        // Find form by itemId
        var itemId = 'maintenance_type1_filterform' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var form = Ext.ComponentQuery.query('#' + itemId)[0];
        return form;
    },
        
    getFormToolBar: function(config)
    {
        // Find form toolbar by itemId
        var itemId = 'maintenance_type1_formtoolbar' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var toolbar = Ext.ComponentQuery.query('#' + itemId)[0];
        return toolbar;
    },
            
    getGrid: function(config)
    {
        // Find grid by itemId
        var itemId = 'maintenance_type1_grid' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var grid = Ext.ComponentQuery.query('#' + itemId)[0];
        return grid;
    },
        
    getGridToolBar: function(config)
    {
        // Find grid toolbar by itemId
        var itemId = 'maintenance_type1_gridtoolbar' + '_' +
                        config.module_id + '_' +
                        config.model.id;
                
        var toolbar = Ext.ComponentQuery.query('#' + itemId)[0];
        return toolbar;
    },
        
    getGridPanel: function(config)
    {
        // Find grid panel by itemId
        var itemId = 'maintenance_type1_gridpanel' + '_' +
                        config.module_id + '_' +
                        config.model.id;
                
        var gridpanel = Ext.ComponentQuery.query('#' + itemId)[0];
        return gridpanel;
    },
        
    setGridPanelTitle: function(config, store)
    {
        var me = this;
        var gridpanel = me.getGridPanel(config);
        var title = gridpanel.title;
        var pos = title.indexOf(" (");
        if (pos >= 0)
        {
            title = title.substr(0, pos);
        }
        title += " (" + store.getCount() + ")";
        gridpanel.setTitle(title);
    },
    
    refreshGrid: function(config)
    {
        var grid = this.getGrid(config);
        var store = grid.getStore(); 

        var scroll_position = grid.getEl().down('.x-grid-view').getScroll();

        store.on('load', function(this_store, records, successful, eOpts)
        {
            // Set scroll
            var task = new Ext.util.DelayedTask(function(){
                grid.getEl().down('.x-grid-view').scrollTo('top', scroll_position.top, false);
            });        
            task.delay(100);

        }, store, {single: true});
        
        // Set params
        var params = {
            stale: false,
            module_id: config.module_id,
            model_id: config.model.id,
            start: 0,
            limit: 9999            
        };
        
        if (config.filterForm)
        {
            var filter_form = this.getFilterForm(config);
            var filter_form_values = filter_form.getValues(); 
            
            // Add data form values to params
            for (var key in filter_form_values) {
                params[key] = filter_form_values[key];
            }
        }
        
        store.load({
            params: params
        });
    },
            
    editRecord: function(config, record)
    {
        // Disabled fields on edit
        var form = this.getForm(config);
        this.setDisabledFieldsOnEdit(true, form);
        // Load form
        if (record !== null)
        {
            // Update the current loaded record var
            this.getMaintenanceView(config).current_loaded_record = record;
            // Load record
            //console.log(record);
            form.getForm().loadRecord(record);
            
            // Fire saved record event
            form.fireEvent('editedRecord', record.data.id);            
        }
    },
            
    newRecord: function(config)
    {
        var form = this.getForm(config);
        // Enabled fields on edit
        this.setDisabledFieldsOnEdit(false, form);
        // Clean form
        form.reset();
        //extjs6 form.clearDirty();
//        this.getForm(config).getForm().reset(); // Doesn't works
        // Set focus
        this.setFocusFieldOnNew(form);        
        // Deselect all grid
        var grid = this.getGrid(config);
        grid.getSelectionModel().deselectAll();
        // Reset the current loaded record property
        this.getMaintenanceView(config).current_loaded_record = null;
        
        // Fire new record event
        form.fireEvent('newRecord');
    },
            
    undoRecord: function(config)
    {
        if (!this.isNewRecord(config))
        {
            this.editRecord(config, this.getCurrentRecord(config));
        }
        else
        {
            this.newRecord(config);
        }
    },
    
    isNewRecord: function(config)
    {
        return (this.getMaintenanceView(config).current_loaded_record === null);
    },
    
    getCurrentRecord: function(config)
    {
        return this.getMaintenanceView(config).current_loaded_record;
    },
            
    deleteRecord: function(config)
    {
        var me = this;
        var form = this.getForm(config);
        var record_id, params;
        
        if (me.isNewRecord(config))
        {
            Ext.MessageBox.show({
               title: me.trans('delete_record'),
               msg: me.trans('select_register_previously'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }
        
        /*if (form.getForm().isDirty())
        {
            Ext.MessageBox.show({
               title: me.trans('delete_record'),
               msg: me.trans('some_fields_have_been_modified'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }*/
            
        Ext.MessageBox.show({
            title: me.trans('delete_record'),
            msg: me.trans('are_you_sure_to_delete'),
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION,
            fn: function(btn, text)
            {
                if(btn === 'yes')
                {
                    record_id = me.getCurrentRecord(config).data.id;
                    
                    var delete_controller = 'core\\backend\\controller\\maintenance\\type1';
                    if(config.delete_controller)
                    {
                        delete_controller = config.delete_controller;
                    }
        
                    // The ajax params
                    params = {
                        controller: delete_controller, 
                        method: 'deleteRecord',
                        module_id: config.module_id,
                        model_id: config.model.id,
                        record_id: record_id
                    };        

                    Ext.Ajax.request(
                    {
                        type: 'ajax',
                        url : 'index.php',
                        method: 'GET',
                        params: params,
                        waitMsg : me.trans('deleting_record'),
                        success: function(response, opts)
                        {
                            var obj = Ext.JSON.decode(response.responseText);
                            if(obj.success)
                            {
                                // It also works!!  
                                var grid = me.getGrid(config);
                                //var store = grid.getStore();                      
                                // It also works!!
                                var store = config.store;
                    
                                var scroll_position = grid.getEl().down('.x-grid-view').getScroll();

                                store.on('load', function(this_store, records, successful, eOpts)
                                {
                                    me.newRecord(config);
                        
                                    // Set scroll
                                    var task = new Ext.util.DelayedTask(function(){
                                        grid.getEl().down('.x-grid-view').scrollTo('top', scroll_position.top, false);
                                    });        
                                    task.delay(100);
                                    
                                }, store, {single: true});

                                store.reload();
                            }
                            else
                            {
                                Ext.MessageBox.show({
                                   title: me.trans('deleting_record_failed'),
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
                               title: me.trans('deleting_record_failed'),
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
            
    cloneRecord: function(config)
    {
        var me = this;
        var form = this.getForm(config);
        var record_id;
        
        if (me.isNewRecord(config))
        {
            Ext.MessageBox.show({
               title: me.trans('clone_record'),
               msg: me.trans('select_register_previously'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }
        
        /*if (form.getForm().isDirty())
        {
            Ext.MessageBox.show({
               title: me.trans('clone_record'),
               msg: me.trans('some_fields_have_been_modified'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }*/
            
        record_id = me.getCurrentRecord(config).data.id;
        
        var fields = form.query('[_clonable=true]');
        if (Ext.isEmpty(fields))
        {
            Ext.MessageBox.show({
               title: me.trans('clone_record'),
               msg: 'There aren\'t any clonable field',
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.ERROR
            });
            return false;
        }
        
        var clonedFields = [];
        Ext.each(fields, function(field) {
            var clonedField = field.cloneConfig();
            if (!Ext.isEmpty(field.store))
            {
                clonedField.store = me.cloneStore(field.store);    
            }
            clonedFields.push(clonedField);
        });  
           
        var code = form.getForm().findField('code').getValue();
        var title = me.trans('cloning_of') + ' ' + code;
        
//        var task = new Ext.util.DelayedTask(function(){
            var cloneWindow = Ext.widget('maintenance_cloneform', {
                title: title,
                record_id: record_id,
                config: config,
                clonedFields: clonedFields
            });        
            cloneWindow.show();
//        });        
//        task.delay(500);
        
        return true;
    },
            
    saveRecord: function(config, publish)
    {
        var me = this;
        var form = this.getForm(config);
        
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
        var form = this.getForm(config);
        var params, discard_fields;
        var is_new_record = false;
        var record_id = '';
        var save_controller = 'core\\backend\\controller\\maintenance\\type1';
        var save_method = 'saveRecord';
        
        if (me.isNewRecord(config))
        {
            is_new_record = true;
        }
        else
        {
            record_id = me.getCurrentRecord(config).data.id;
        }
        
        // Check for overrided savecontroller and savemethod definition
        if (config.save_controller)
        {
            save_controller = config.save_controller;
        }
        if (config.save_method)
        {
            save_method = config.save_method;
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
                    //console.log(new_record);
                    var code = me.getCodeFieldValue(form);
                    // Fire saved record event
                    form.fireEvent('savedRecord', code, publish);
                    
                    // It also works!!  
                    var grid = me.getGrid(config);
                    //var store = grid.getStore();
                    // It also works!!
                    var store = config.store;
                    
                    var scroll_position = grid.getEl().down('.x-grid-view').getScroll();
                    
                    if (is_new_record)
                    {
                        store.add(new_record);
                        me.newRecord(config);
                    }
                    else
                    {
                        // Replace record
                        var index = store.indexOf(me.getCurrentRecord(config));
                        store.removeAt(index);
                        store.on('add', function(this_store, records, idx, eOpts) 
                        {
                            var record = store.getAt(idx);
                            me.editRecord(config, record);
                            
                            // Select row on grid
                            //console.log(idx);
                            grid.getSelectionModel().deselectAll(true);
                            grid.getSelectionModel().select(idx);
                        
                        }, this, {single: true});
                        store.insert(index, new_record);
                    }
                        
                    // Set scroll
                    var task = new Ext.util.DelayedTask(function(){
                        grid.getEl().down('.x-grid-view').scrollTo('top', scroll_position.top, false);
                    });        
                    task.delay(100);
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
    
    publishRecord: function(config)
    {
        var me = this;
        var form = this.getForm(config);
        
        if (me.isNewRecord(config))
        {
            Ext.MessageBox.show({
               title: me.trans('publish_record'),
               msg: me.trans('select_register_previously'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }
        
        /*if (form.getForm().isDirty())
        {
            Ext.MessageBox.show({
               title: me.trans('publish_record'),
               msg: me.trans('some_fields_have_been_modified'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }*/
            
        if (is_super_user)
        {
            me.finallyPublishRecord(config);
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
                        me.finallyPublishRecord(config);
                    }
                }
            });            
        }
        
        return true;
    },
    
    finallyPublishRecord: function(config)
    {
        var me = this;
        var record_id, params;
        var publish_controller = 'core\\backend\\controller\\maintenance\\type1';
        
        record_id = me.getCurrentRecord(config).data.id;

        // Check for overrided publish controller
        if(config.publish_controller)
        {
            publish_controller = config.publish_controller;
        }

        // The ajax params
        params = {
            controller: publish_controller, 
            method: 'publishRecord',
            module_id: config.module_id,
            model_id: config.model.id,
            record_id: record_id
        };

        Ext.getBody().mask(me.trans('publishing_record'));  

        Ext.Ajax.request(
        {
            type: 'ajax',
            url : 'index.php',
            method: 'GET',
            params: params,
            //waitMsg : me.trans('publishing_record'),
            success: function(response, opts)
            {
                Ext.getBody().unmask();
                var obj = Ext.JSON.decode(response.responseText);
                if(obj.success)
                {
                    // It also works!!  
                    var grid = me.getGrid(config);
                    //var store = grid.getStore();                      
                    // It also works!!
                    var store = config.store;  
                    
                    var scroll_position = grid.getEl().down('.x-grid-view').getScroll();

                    var index = store.indexOf(me.getCurrentRecord(config));
                    store.on('load', function(this_store, records, successful, eOpts)
                    {
                        var record = this_store.getAt(index);
                        me.editRecord(config, record);
                        
                        // Set scroll
                        var task = new Ext.util.DelayedTask(function(){
                            grid.getEl().down('.x-grid-view').scrollTo('top', scroll_position.top, false);
                        });        
                        task.delay(100);

                    }, store, {single: true});

                    store.reload();
                }
                else
                {
                    Ext.MessageBox.show({
                       title: me.trans('publishing_record_failed'),
                       msg: obj.data.result,
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.ERROR
                    });
                }
            },
            failure: function(form, data)
            {
                Ext.getBody().unmask();
                var obj = Ext.JSON.decode(data.response.responseText);
                Ext.MessageBox.show({
                   title: me.trans('publishing_record_failed'),
                   msg: obj.data.result,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
                });
            }
        });        
    },
            
    publishAllRecords: function(config)
    {
        var me = this;
        //var form = this.getForm(config);
        var records_id = [];
        var params, json_records_id;
        var store = config.store;
        var publish_controller = 'core\\backend\\controller\\maintenance\\type1';
        
        if (Ext.isEmpty(store.data.items))
        {
            Ext.MessageBox.show({
               title: me.trans('publish_all_records'),
               msg: me.trans('there_are_not_records_to_publish'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }
        
        /*if (form.getForm().isDirty())
        {
            Ext.MessageBox.show({
               title: me.trans('publish_all_records'),
               msg: me.trans('some_fields_have_been_modified'),
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.WARNING
            });
            return false;
        }*/
            
        Ext.MessageBox.show({
            title: me.trans('publish_all_records'),
            msg: me.trans('are_you_sure_to_publish_all_records'),
            buttons: Ext.MessageBox.YESNO,
            icon: Ext.MessageBox.QUESTION,
            fn: function(btn, text)
            {
                if(btn === 'yes')
                {
                    // Get all store records
                    Ext.each(store.data.items, function(item) {
                        records_id.push(item.data.id);
                        //console.log(item.data.id);
                    });                    
                    json_records_id = Ext.encode(records_id);
        
                    // Check for overrided publish controller
                    if(config.publish_controller)
                    {
                        publish_controller = config.publish_controller;
                    }

                    // The ajax params
                    params = {
                        controller: publish_controller, 
                        method: 'publishAllRecords',
                        module_id: config.module_id,
                        model_id: config.model.id,
                        records_id: json_records_id
                    };

                    Ext.getBody().mask(me.trans('publishing_all_records'));
                    
                    Ext.Ajax.request(
                    {
                        type: 'ajax',
                        url : 'index.php',
                        method: 'POST',
                        params: params,
                        //waitMsg : me.trans('publishing_all_records'),
                        success: function(response, opts)
                        {
                            Ext.getBody().unmask();
                            var obj = Ext.JSON.decode(response.responseText);
                            if(obj.success)
                            {
                                Ext.MessageBox.show({
                                   title: me.trans('publish_all_records'),
                                   msg: me.trans('publishing_has_been_successful'),
                                   buttons: Ext.MessageBox.OK,
                                   icon: Ext.MessageBox.INFO
                                });
                            }
                            else
                            {
                                Ext.MessageBox.show({
                                   title: me.trans('publishing_all_records_failed'),
                                   msg: obj.data.result,
                                   buttons: Ext.MessageBox.OK,
                                   icon: Ext.MessageBox.ERROR
                                });
                            }
                        },
                        failure: function(form, data)
                        {
                            Ext.getBody().unmask();
                            var obj = Ext.JSON.decode(data.response.responseText);
                            Ext.MessageBox.show({
                               title: me.trans('publishing_all_records_failed'),
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
    
    exportRecords: function(config)
    {
        var export_controller = 'core\\backend\\controller\\maintenance\\type1';

        if (config.export_controller)
        {
            export_controller = config.export_controller;
        }
        
        var url = '/index.php?' + 
                   'controller=' + export_controller +
                   '&method=exportRecords' + 
                   '&module_id=' + config.module_id + 
                   '&model_id=' + config.model.id;
        console.log(url);

        window.open(url);     
    },
            
    refreshForm: function(config)
    {
        var me = this;
        var form = this.getForm(config);

        // Reload multiselect and combo stores        
        var fields = form.query('combo, multiselect');
        if (fields)
        {
            Ext.each(fields, function(field) {
                field.store.reload();            
            });            
        }           
    },
            
    setDisabledFieldsOnEdit: function(disabled, form)
    {
        // Disabled fields on edit
        var fields = form.query('textfield[_disabledOnEdit=true], ' +
                                'numberfield[_disabledOnEdit=true], ' +
                                'checkboxfield[_disabledOnEdit=true], ' +
                                'combo[_disabledOnEdit=true]'
                               );
        if (fields)
        {
            Ext.each(fields, function(item) {
                item.setDisabled(disabled);
            });
        }
    },
            
    setFocusFieldOnNew: function(form)
    {
        // Set focus
        var field = form.down('textfield[_setFocusOnNew=true], ' +
                              'numberfield[_setFocusOnNew=true], ' +
                              'combo[_setFocusOnNew=true]'
                             );
        if (field)
        {
            field.focus();
        }
    },
            
    addSubmitDisabledFields: function(params, form)
    {
        var fields = form.query('textfield[disabled=true], ' +
                                'numberfield[disabled=true], ' +
                                'checkboxfield[disabled=true], ' +
                                'combo[disabled=true]');
        var name, value;
        
        if (fields)
        {
            Ext.each(fields, function(item) {
                name = item.name;
                value = item.value;
                params[name] = value;                    
            }, this);
        }
        
        return params;
    },
            
    addSubmitUncheckedFields: function(params, form)
    {
        var fields = form.query('checkboxfield[checked=false]');
        var name, value;
        
        if (fields)
        {
            Ext.each(fields, function(item) {
                name = item.name;
                value = '';
                params[name] = value;
            });
        }
        
        return params;
    },
            
    addSubmitValuesFromComboFields: function(params, form)
    {
        var fields = form.query('combo[_addSubmitValues]');
        var name, value, record;
        if (fields)
        {
            Ext.each(fields, function(item) {
                record = item.findRecord(item.valueField, item.value);
                Ext.each(item._addSubmitValues, function(values) {
                    name = values.as;
                    if (record)
                    {                    
                        value = record['data'][values.field];
                        
                    }
                    else
                    {
                        value = '';
                    }
                    params[name] = value;
                });
            });
        }
        
        return params;
    },
            
    addSubmitValuesFromGridPanels: function(params, form)
    {
        var gridpanels = form.query('gridpanel[_property]');
        var name, records;
        if (gridpanels)
        {
            Ext.each(gridpanels, function(gridpanel) {
                var data = gridpanel.getStore().getRange();   
                records = [];
                if(!Ext.isEmpty(data))
                {
                    Ext.each(data, function(rc)
                    { 
                        records.push(Ext.apply(rc.data));
                    });    
                }   
                records = Ext.encode(records);
                name = gridpanel._property;
                params[name] = records;                
            });
        }
        
        return params;
    },
            
    getCodeFieldValue: function(form)
    {
        var ret = null;
        var field = form.down('[name=code]');
        if (field)
        {
            ret = field.value;
        }
        return ret;
    },
    
    addListeners: function(form)
    {        
        var me = this; 
        var form_id = form.getId();
        var fields = form.query('textfield, numberfield, combo');
        
        if (fields)
        {
            Ext.each(fields, function(item) {
                var specialkey_event_allowed = true;
                if (!Ext.isEmpty(item._discardListeners))
                {
                    if (Ext.Array.contains(item._discardListeners, 'specialkey'))
                    {
                        specialkey_event_allowed = false;
                    }
                }
                if (specialkey_event_allowed)
                {
                    item.on('specialkey', function(field, e, eOpts)
                    {
                        if (e.getKey() === e.ENTER && field.isValid()) 
                        {
                            e.stopEvent();

                            //var f = me.getComponentQuery('form', config);
                            var f = Ext.getCmp(form_id);
                            
                            // Focus the next field if it exists
                            var nextField = me.getNextField(field, f);
                            nextField && nextField.focus();                      
                        }                    
                    }, this);                      
                }
            });
        }        
    },
    
    getNextField: function(field, form)
    {
        var me = this;
        var fields = form.query('textfield, numberfield, combo');
        if (!fields)
        {
            return false;
        }   
        
        var currentFieldIdx = fields.indexOf(field);    
        if(currentFieldIdx <= -1) 
        {
            return false;
        }
        
        // Jump to specific field?
        var current_field = fields[currentFieldIdx];
        if (!Ext.isEmpty(current_field._onEnterJumpTo))
        {
            return form.getForm().findField(current_field._onEnterJumpTo);
        }
        else if (current_field._onEnterJumpToSubmitButton)
        {
            var submitButton = me.getSubmitButton(form);
            if (submitButton && submitButton.isVisible() && !submitButton.isDisabled())
            {
                return submitButton;
            }
        }
        
        
        // Get the next form field
        var nextField, i=1;
        while (true) {
            //console.log(i);
            nextField = fields[currentFieldIdx + i];
            
            if (!nextField || nextField.getXType() === 'textareafield')
            {
                var submitButton = me.getSubmitButton(form);
                if (submitButton && submitButton.isVisible() && !submitButton.isDisabled())
                {
                    return submitButton;
                }
                return false;
            }
            
            var value = nextField.getValue();
            if (!nextField.isHidden() && !nextField.isDisabled() && !nextField.readOnly && (Ext.isEmpty(value) || value == 0 || nextField._notJumpEvenIfThisFieldHasValue))
            {
                //console.log(nextField);
                return nextField;
            }
            i += 1;
        };

        return false;
    },
    
    getSubmitButton: function(form)
    {
        var submitButton = form.down('button[_isSubmitButton=true]');
        if (submitButton) return submitButton;
        
        submitButton = form.down('button[formBind=true]');
        if (submitButton) return submitButton;
            
        submitButton = form.down('button[iconCls=x-fa fa-save]');
        if (submitButton) return submitButton;
        
        return null;
    },
    
    updateFormProperties: function(form)
    {
        var me = this;
        var items = form.getForm().getFields().items,
            i = 0,
            len = items.length;
        for(; i < len; i++) {
            var field = items[i];
            if (field.allowBlank === false)
            {
                field.blankText = me.trans('this_field_is_required');
                field.msgTarget = 'side';
            }
            if (field._defaultValue)
            {
//                field.suspendEvents(false); // Stop all events. 
                field.setValue(field._defaultValue);
//                field.resumeEvents(); // resume events
            }
        }       
    },    
    
    hideRefreshButtonForm: function(config, form)
    {
        // Hide refresh button if there isn't any combo or multiselect
        var fields = form.down('combo, multiselect');
        if (!fields)
        {
            var toolbar = this.getFormToolBar(config);
            var button = toolbar.down('#refresh_button_form');
            button.setVisible(false);
        }          
    },
    
    setComboStores: function(form)
    {
        var me = this,
            fields = form.query('combo');//, multiselect');
        if (fields)
        {
            Ext.each(fields, function(item) {
                if (item._store)
                {
                    
                    var config = 
                    {
                        module_id: item._store.module_id,
                        model: 
                        {
                            id: item._store.model_id,
                            fields: item._store.fields,
                            filters: item._store.filters,
                            add_data: item._store.add_data
                        },
                        get_concrete_fields: item._store.fields
                    };       
                    
                    if (item._store.get_controller)
                    {
                        config.get_controller = item._store.get_controller;
                    }
                 
                    var autoload = true;      
                    if (item._store.autoload)
                    {
                        autoload = (item._store.autoload === 'yes');
                    }
                    
                    var store = me.getGetRecordsStore(config, autoload, false, 'true');
                    item.setStore(store);
                }    
            });
        }       
    },  
    
    getGetRecordsStore: function(config, autoload, doLoad, stale)
    {
        var me = this;
        var params, filters, add_data, discard_fields, get_concrete_fields;
        var get_controller = 'core\\backend\\controller\\maintenance\\type1';
        
        var model_name = config.model.id + '-' + me.getRandom(1, 100);
        
        // The model
        Ext.define(model_name, {
            extend: 'Ext.data.Model',
            fields: config.model.fields
        });
        
        // Get records controller
        if(config.get_controller)
        {
            get_controller = config.get_controller;
        }
        
        // The ajax params
        params = {
            controller: get_controller, 
            method: 'getRecords',
            module_id: config.module_id,
            model_id: config.model.id,
            stale: stale,
            filters: config.filters
        };        
        
        // Add filters to params
        if (config.model.filters)
        {
            filters = Ext.JSON.encode(config.model.filters);    
            params['filters'] = filters;
        }
        
        // Add data to fill to the store
        if (config.model.add_data)
        {
            add_data = Ext.JSON.encode(config.model.add_data);    
            params['add_data'] = add_data;
        }
        
        // Add fields or properties to discard
        if (config.model.discard_fields)
        {
            discard_fields = Ext.JSON.encode(config.model.discard_fields);    
            params['discard_fields'] = discard_fields;
        }      
        
        // Add 'get only fields'
        if (config.get_concrete_fields)
        {
            get_concrete_fields = Ext.JSON.encode(config.get_concrete_fields);    
            params['get_concrete_fields'] = get_concrete_fields;
        }   
        
        // Add sorters
        var sorters = (config.model.sorters)? config.model.sorters : [];
        
        // The store
        var groupField = (config.grid && config.grid.groupField)? config.grid.groupField : null;
        config.store = Ext.create('Ext.data.Store', {
            model: model_name,
            autoLoad: autoload,
            groupField: groupField,
            proxy: {
                type: 'ajax',
                url : 'index.php',
                extraParams: params,
                reader: {
                    type: 'json',
                    rootProperty: 'data.results',
                    totalProperty: 'data.total'
                }
            },
            sorters: sorters,
            remoteSort: false
        });
        
        if (doLoad)
        {
            config.store.load({
                params: {
                    module_id: config.module_id,
                    model_id: config.model.id,
                    start: 0,
                    limit: 9999
                }
            });
        }
        
        return config.store;
    },
    
    getRecord: function(config)
    { 
        var me = this;
        var record_id;
        
        if (me.isNewRecord(config))
        {
            return null;
        }
        
        record_id = me.getCurrentRecord(config).data.id; 
        
        Ext.getBody().mask(me.trans('loading'));

        Ext.Ajax.request({
            type: 'ajax',
            url : 'index.php',
            method: 'GET',
            params: {
                controller: 'core\\backend\\controller\\maintenance\\type1', 
                method: 'getRecord',
                module_id: config.module_id,
                model_id: config.model.id,
                record_id: record_id           
            },
            success: function(response, opts)
            {
                Ext.getBody().unmask();
                var obj = Ext.JSON.decode(response.responseText);
                if (!obj.success)
                {
                    Ext.MessageBox.show({
                       title: 'Error getting record',
                       msg: obj.data.result,
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.INFO
                    });
                    me.fireEvent('getRecord', false, null);
                    return;
                }

                //console.log(obj.data.result);
                var data = obj.data.result.data;
                /*var model = obj.data.result.model;
        
                var model_name = 'recordModel' + '-' + me.getRandom(1, 100);
                
                Ext.define(model_name, {
                    extend: 'Ext.data.Model',
                    fields: model
                });

                var store = Ext.create('Ext.data.Store', {
                    model: model_name,
                    data : data
                });

                console.log(model_name);
                console.log(data);                
                console.log(store);
                
                var record = store.data.items[0];*/
                var record = Ext.data.Record.create(data);
                
                me.fireEvent('getRecord', true, record);
            },
            failure: function(form, data)
            {
                Ext.getBody().unmask();
                me.fireEvent('getRecord', false, null);
            }
        });          
    },
    
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    }
});