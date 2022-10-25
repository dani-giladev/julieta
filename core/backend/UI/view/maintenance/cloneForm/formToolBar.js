Ext.define('App.core.backend.UI.view.maintenance.cloneForm.formToolBar', {
    extend: 'Ext.toolbar.Toolbar',
    
    alias: 'widget.maintenance_cloneform_formtoolbar',
        
    explotation: 'Clone form toolbar view',

    region: 'south',
                
    border: true,
    frame: false,

    record_id: null,
    config: null,
    
//    ui: 'footer',
    
    initComponent: function() {
        
        var me = this;
        
        this.title = '';
        
        this.items = 
        [
            { xtype: 'tbfill' },
            {
                text: me.trans('clone'),
                handler: me.cloneRecord
            },    
            {
                text: me.trans('cancel'),
                handler: me.cancel
            }        
        ];
            
        this.callParent(arguments);
    },
            
    cloneRecord: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().cloneRecord(me.record_id, me.config);
    },
            
    cancel: function(button, eventObject)
    {
        var window = button.up('window');
        window.close();
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.cloneForm');       
        return controller;
    }
    
});