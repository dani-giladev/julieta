Ext.define('App.core.backend.UI.view.maintenance.type1.formToolBar', {
    extend: 'Ext.toolbar.Toolbar',
    
    alias: 'widget.maintenance_type1_formtoolbar',
        
    explotation: 'Maintenance form toolbar view',

    region: 'north',
                
    border: true,
    frame: false,
    
//    ui: 'footer',
    
    config: null,
    
    initComponent: function() {
        
        var me = this;
        
        this.itemId = 'maintenance_type1_formtoolbar' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        this.title = '';
        
        this.items = 
        [
            {
                text: me.trans('save'),
                iconCls: 'x-fa fa-save',
                disabled: !me.config.permissions.update,
                handler: me.saveRecord
            },      
            {
                text: me.trans('save_and_publish'),
                iconCls: 'x-fa fa-flag-checkered',
                disabled: (!me.config.permissions.update || !me.config.permissions.publish),
                hidden: !me.config.enable_publication,
                handler: me.saveAndPublishRecord
            },   
            { xtype: 'tbfill' },
            {
                text: me.trans('new'),
                iconCls: 'x-fa fa-file-o',
                handler: me.newRecord,
                disabled: !me.config.permissions.update
            },             
            {
                text: me.trans('undo'),
                iconCls: 'x-fa fa-undo',
                handler: me.undoRecord,
                disabled: !me.config.permissions.update
            },     
            {
                itemId: 'refresh_button_form',
                text: me.trans('refresh'),
                iconCls: 'x-fa fa-refresh',
                handler: me.refreshForm
            }        
        ];
            
        this.callParent(arguments);
    },
            
    newRecord: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().newRecord(me.config);
    },
            
    undoRecord: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().undoRecord(me.config);
    },
            
    refreshForm: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().refreshForm(me.config);
    },
            
    saveRecord: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().saveRecord(me.config, false);
    },
            
    saveAndPublishRecord: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().saveRecord(me.config, true);
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