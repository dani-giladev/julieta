Ext.define('App.core.backend.UI.view.maintenance.basic.basic', {
    extend: 'App.core.backend.UI.view.maintenance.type1.maintenance',
    
    alias: 'widget.maintenance_basic',
        
    explotation: 'Common basic view',

    config: null,
    
    initComponent: function() {
        this.alert();
        
        // General properties
        this.initGeneralProperties();
        // The grid
        this.initGrid();
        // The form
        this.initForm();
        // Set titles
        this.setTitles();

        this.callParent(arguments);           
        
        var form = App.app.getController('App.core.backend.UI.controller.maintenance.type1').getForm(this.config);
        form.on('newRecord', this.onNewRecord);
        form.on('editedRecord', this.onEditedRecord);
    },
    
    initGeneralProperties: function()
    {
        this.config.hide_datapanel_title = true;               
        this.config.enable_publication = false;
        this.config.enable_deletion = true;
    },
            
    initGrid: function()
    {
        var me = this;
        me.config.grid = 
        {
            title: '',
            columns: 
            [
                {
                    text: me.trans('code'),
                    dataIndex: 'code',
                    _renderer: 'bold',
                    width: 100
                },
                {
                    text: me.trans('name'),
                    dataIndex: 'name',
                    flex: 1,
                    align: 'left',
                    minWidth: 180
                },
                {
                    text: me.trans('available'),
                    dataIndex: 'available',
                    width: 90
                }
            ]
        };
    },
            
    initForm: function()
    {
        var me = this;
        
        me.config.form =
        {
            title: '',
            fields:
            [
                me.getMainFieldset(),
                me.getPropertiesFieldset()
            ]
        };
    },
    
    setTitles: function()
    {
        //this.config.grid.title = 'The grid';
        //this.config.form.title = 'The form';
    },
    
    getMainFieldset: function()
    {
        var me = this;
        var ret =  
        {
            xtype: 'fieldset',
            padding: 5,
            title: me.trans('main'),
            anchor: '100%',
            items: 
            [
                {
                    xtype: 'textfield',
                    name: 'code',
                    fieldLabel: '<b>' + me.trans('code') + '</b>',
                    maskRe: /[a-zA-Z0-9\-\_]/,
                    allowBlank: false,
                    labelAlign: 'right',
                    _disabledOnEdit: true,
                    _setFocusOnNew: true
                }                
            ]
        };
        
        return ret;
    },
    
    getPropertiesFieldset: function()
    {
        var me = this;
        var ret =  
        {
            xtype: 'fieldset',
            padding: 5,
            title: me.trans('properties'),
            anchor: '100%',
            items: 
            [
                {
                    xtype: 'textfield',
                    name: 'name',
                    fieldLabel: me.trans('name'),
                    allowBlank: false,
                    labelAlign: 'right',
                    anchor: '100%'
                },
                {
                    xtype: 'textfield',
                    name: 'description',
                    fieldLabel: me.trans('description'),
                    allowBlank: true,
                    labelAlign: 'right',
                    anchor: '100%'
                },
                {
                    xtype: 'checkboxfield',
                    name: 'available',
                    fieldLabel: me.trans('available'),
                    boxLabel: '',
                    labelAlign: 'right',                
                    anchor: '100%',
                    _defaultValue: true // checked when new record
                }                              
            ]
        };
        
        return ret;
    },
    
    onRender: function(form, eOpts)
    {
        this.callParent(arguments);
    },
            
    onNewRecord: function()
    {

    },
            
    onEditedRecord: function(id)
    {

    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    },
            
    alert: function()
    {
        App.app.getController('App.core.backend.UI.controller.common').alertInitMaintenance(this.config);              
    }
});