Ext.define('App.core.backend.UI.view.maintenance.type1.grid', {
    extend: 'Ext.grid.Panel',
    
    alias: 'widget.maintenance_type1_grid',
    
    explotation: 'Maintenance grid view',

    region: 'center',

    border: false,
    frame: false,
    
    config: null,
        
    initComponent: function()
    {
        var me = this;
        
        me.itemId = 'maintenance_type1_grid' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        me.title = '';        
        me.store = me.config.store;
        
        me.store.on('datachanged', function(store, eOpts)
        {
            me.getViewController().setGridPanelTitle(me.config, store);
        }); 
        
        me.viewConfig = {
            emptyText: me.trans('there_are_not_records_to_show'),
            deferEmptyText: false,
            listeners: {
                scope: me,
                itemcontextmenu: me.onContextMenu
            }
        };
        
        if (me.config.grid.features)
        {
            me.features = me.config.grid.features;
        }
        
        if (me.config.grid.plugins)
        {
            me.plugins = me.config.grid.plugins;
        }
        
        me.columns = me.config.grid.columns;
        me.columns.push(
            {
                text: me.trans('created_by'),
                dataIndex: 'createdBy',
                width: 110,
                align: 'center'
            },
            {
                text: me.trans('creation_datetime'),
                dataIndex: 'creationDateTime',
                width: 150,
                align: 'center'
            },
            {
                text: me.trans('modified_by'),
                dataIndex: 'modifiedBy',
                width: 110,
                align: 'center'
            },
            {
                text: me.trans('last_modification'),
                dataIndex: 'lastModificationDateTime',
                width: 150,
                align: 'center'
            }
        );
        // Add rendering format to the column
        me.columns = me.addRenderFormatColumn();
        
        me.callParent(arguments);
        me.on('selectionchange', me.onSelect, me);
        me.on('itemclick', me.onItemClick, me);
        
    },
    
    /* 
     * Important!!!
     * For the common maintenance development, we have to define all the events in the view.  
     * We work with dynamic itemId properties and is very hard to place these events in the controller.
     */
    
    onRender: function(grid, eOpts)
    {
        
        this.callParent(arguments);
    },
    
    onContextMenu: function(view, record, item, index, e, eOpts)
    {
        var me = this;
        
        if (!me.config.grid.contextmenu)
        {
            return null;
        }
        
        var menu = Ext.create('Ext.menu.Menu', 
        {
            items: me.config.grid.contextmenu
        });
        
        e.stopEvent();
        menu.showAt(e.getXY());
        e.preventDefault();
    },
    
    getGridContextMenu: function(grid)
    {
        return null;
    },
    
    onSelect: function(grid, record, index, eOpts)
    {
//        console.log(record);
    },
        
    onItemClick: function(grid, record, item, index, e, eOpts)
    {
        if (this.config.form && record)
        {
            this.getViewController().editRecord(this.config, record);
        }
        
        this.fireEvent('onItemClicked', record);
    },

    addRenderFormatColumn: function()
    {
        var me = this;
        var columns = me.config.grid.columns;
        var fields_model = me.config.model.fields;
        
        Ext.each(columns, function(column) {
            if (column._renderer)
            {
                if (column._renderer === 'boolean')
                {
                    column.renderer = me.formatBoolean;
                    column.align = 'center';
                }
                else if (column._renderer === 'bold')
                {
                    column.renderer = me.formatBold;
                }
                else if (column._renderer === 'date')
                {
                    column.renderer = me.formatDate;
                    column.align = 'center';
                }
            }
            else
            {
                Ext.each(fields_model, function(field_model) {
                    if (column.dataIndex === field_model.name)
                    {
                        if (field_model.type === 'boolean')
                        {
                            column.renderer = me.formatBoolean;
                            column.align = 'center';
                        }
                        else if (field_model.type === 'bold')
                        {
                            column.renderer = me.formatBold;
                        }
                        else if (field_model.type === 'date')
                        {
                            column.renderer = me.formatDate;
                            column.align = 'center';
                        }
                    }
                });
            }
        });
        
        return columns;
    },
            
    formatBoolean: function(value)
    {
        return Ext.String.format('<img src="resources/ico/'+(value ? 'true' : 'false')+'.png" />');
    },
            
    formatBold: function(value)
    {
        return '<b>' + value + '</b>';
    },
            
    formatDate: function(value)
    {
        return Ext.Date.format(value, app_dateformat);
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1');       
        return controller;
    }
            
    
});