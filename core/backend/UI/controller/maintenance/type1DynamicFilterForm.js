Ext.define('App.core.backend.UI.controller.maintenance.type1DynamicFilterForm', {
    extend: 'App.core.backend.UI.controller.common',

    requires: [
        'App.core.backend.UI.view.maintenance.type1.dynamicFilterForm'
    ],
    
    getGrid: function(config)
    {
        // Find grid by itemId
        var itemId = 'maintenance_type1_grid' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var grid = Ext.ComponentQuery.query('#' + itemId)[0];
        return grid;
    },
            
    getForm: function(config)
    {
        // Find form by itemId
        var itemId = 'maintenance_type1_dynamicfilterform' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var form = Ext.ComponentQuery.query('#' + itemId)[0];
        return form;
    },
            
    getFields: function(config)
    {
        // Find container by itemId
        var itemId = 'maintenance_type1_dynamicfilterform_fields_container' + '_' +
                        config.module_id + '_' +
                        config.model.id;
        var container = Ext.ComponentQuery.query('#' + itemId)[0];
        return container.items.items;
    },
    
    resetFilterForm: function(config)
    {        
        var form = this.getForm(config);
        form.getForm().reset();
    },
    
    initialize: function(config)
    {
        var me = this;
        var fields = me.getFields(config);
        var grid = me.getGrid(config);
        
        Ext.each(fields, function(field) {
            
            // Add listeners
            field.on('change', function(field, newValue)
            {
                me.updateFilters(config);
                if (field._secondChangeListener)
                {
                    field._secondChangeListener();
                }
                
            });
            field.on('afterrender', function(field, newValue)
            {
                if (field._default_value)
                {
                    field.setValue(field._default_value);
                    
                }  
            });
                
            field.value = '';
            field.disabled = false;

            // Set filters
            if (!Ext.isEmpty(config.filters) && !Ext.isEmpty(config.filters[field.name]))
            {
                field.value = config.filters[field.name];
                field.disabled = true;
            }
            
        });
        
        me.updateFilters(config);
        
    },
    
    updateFilters: function(config)
    {
        var me = this;
        var grid = me.getGrid(config);
        var fields = me.getFields(config);
        var filter, values, is_boolean, is_date;
        var store = grid.getStore();
        var filters = store.getFilters();
        filters.removeAll();
        
        Ext.each(fields, function(field)
        {
            is_boolean = (field._filtertype === 'boolean');
            is_date = (field._filtertype === 'date');

            values = me.getFilterValues(field, field.getValue());

            if (values.active)
            {
                filter = {
                    anyMatch: !is_boolean,
                    exactMatch: is_boolean,
                    property: field.name,
                    value: values.value,
                    disabled: !values.active
                };
                
                store.addFilter(filter);
            }           
        });
  
    },
    
    getFilterValues: function(field, newValue)
    {
        var value, active;
        
        if (field.xtype === 'combo')
        {                           
            if (field._filtertype === 'boolean')
            {
                if (newValue === 'yes')
                {
                    //value = '1';
                    value = true;
                    active = true;
                }
                else if (newValue === 'no')
                {
                    //value = '';
                    value = false;
                    active = true;
                }
                else
                {
                    //value = '';
                    value = false;
                    active = false;
                }
            }
            else
            {
                //field._filtertype === 'string'
                if (!newValue || newValue === 'all' || newValue.trim() === '')
                {
                    value = '';
                    active = false;
                }
                else
                {
                    value = newValue;
                    active = true;
                }
            }
        }
        else
        {
            value = newValue;
            active = !Ext.isEmpty(newValue);
        }
                        
        return {
            value: value,
            active: active
        };
    }

});