Ext.define('App.core.backend.UI.view.maintenance.type1ModalForm.toolBar', {
    extend: 'Ext.toolbar.Toolbar',
    
    alias: 'widget.maintenance_type1_modalform_toolbar',
        
    explotation: 'Windowed form Maintenance (toolbar)',

    region: 'north',
                
    border: true,
    frame: false,
    
    config: null,
    
//    ui: 'footer',
    
    initComponent: function() {
        
        var me = this;
        
        this.title = '';
        
        this.items = 
        [     
            {
                text: me.trans('save'),
                disabled: !me.config.permissions.update,
                handler: me.save
            },      
            {
                text: me.trans('save_and_publish'),
                disabled: (!me.config.permissions.update || !me.config.permissions.publish),
                hidden: !me.config.enable_publication,
                handler: me.saveAndPublish
            }            
        ];
            
        this.callParent(arguments);
    },
            
    save: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().saveRecord(me.config, false);
    },
            
    saveAndPublish: function(button, eventObject)
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
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1ModalForm');       
        return controller;
    }
    
});