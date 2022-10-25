Ext.define('App.core.backend.UI.controller.maintenance.cloneForm', {
    extend: 'App.core.backend.UI.controller.maintenance.type1',

    views: [
        'App.core.backend.UI.view.maintenance.cloneForm.cloneForm',
        'App.core.backend.UI.view.maintenance.cloneForm.form',
        'App.core.backend.UI.view.maintenance.cloneForm.formToolBar'
    ],
    
    models: [
        
    ],
    
    stores: [
        
    ],

    refs: [
        
    ],
    
    init: function() 
    {
        this.control({

        }); 
    },
            
    getForm: function(config)
    {
        // Find form by itemId
        var itemId = 'maintenance_cloneform_form' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var form = Ext.ComponentQuery.query('#' + itemId)[0];
        return form;
    },
            
    getWindow: function(config)
    {
        // Find form by itemId
        var itemId = 'maintenance_cloneform_window' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var form = Ext.ComponentQuery.query('#' + itemId)[0];
        return form;
    },
            
    newRecord: function(config)
    {
        var form = this.getForm(config);
//        // Enabled fields on edit
//        this.setDisabledFieldsOnEdit(false, form);
        // Clean form
        //form.reset();
        //extjs6 form.clearDirty();
//        this.getForm(config).getForm().reset(); // Doesn't works
        // Set focus
        this.setFocusFieldOnNew(form);        
    },
    
    cloneRecord: function(record_id, config)
    {
        var me = this;
        var params;
        var form = this.getForm(config);
        var window = this.getWindow(config);
        var clone_controller = 'core\\backend\\controller\\maintenance\\type1';
        
        if(form.getForm().isValid())
        {   
            // Check for overrided clonecontroller definition
            if(config.clone_controller)
            {
                clone_controller = config.clone_controller;
            }
        
            // The ajax params
            params = {
                controller: clone_controller, 
                method: 'cloneRecord',
                module_id: config.module_id,
                model_id: config.model.id,
                record_id: record_id
            }; 
            
            // Add params with checkbox fields with checked=false
            params = me.addSubmitUncheckedFields(params, form);
            // Add params with others submit values from combo fields
            params = me.addSubmitValuesFromComboFields(params, form);

            form.getForm().submit(
            {
                type: 'ajax',
                url : 'index.php',
                method: 'GET',
                params: params,
                waitMsg : me.trans('cloning_record'),
                success: function(thisForm, data)
                {
                    var obj = Ext.JSON.decode(data.response.responseText);
                    if (obj.success)
                    {
                        if (!Ext.isEmpty(obj.data.result))
                        {
                            Ext.MessageBox.show({
                               title: me.trans('cloned_record'),
                               msg: Ext.util.Format.htmlDecode(obj.data.result),
                               buttons: Ext.MessageBox.OK,
                               icon: Ext.MessageBox.INFO
                            });                            
                        }
                        var store = config.store;  
                        store.reload();
                        window.close();
                    }
                    else
                    {
                        Ext.MessageBox.show({
                           title: me.trans('cloning_record_failed'),
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
                       title: me.trans('cloning_record_failed'),
                       msg: obj.data.result,
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.ERROR
                    });
                }
            });            
        }     
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    }
    
});