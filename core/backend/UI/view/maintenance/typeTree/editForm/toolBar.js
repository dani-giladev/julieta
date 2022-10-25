Ext.define('App.core.backend.UI.view.maintenance.typeTree.editForm.toolBar', {
    extend: 'Ext.toolbar.Toolbar',
    
    alias: 'widget.maintenance_typetree_editform_toolbar',
        
    explotation: 'Maintenance tree (edit form toolbar)',

    region: 'north',
                
    border: true,
    frame: false,
    
    config: null,
    is_new_node: null,
    
//    ui: 'footer',
    
    initComponent: function() {
        
        var me = this;
        
        this.title = '';
        
        this.items = 
        [     
            {
                text: me.trans('accept'),
                handler: me.accept
            },
            {
                text: me.trans('cancel'),
                handler: me.cancel
            }             
        ];
            
        this.callParent(arguments);
    },
            
    accept: function(button, eventObject)
    {
        var me = button.up('toolbar');
        if (me.getViewController().accept(me.config, me.is_new_node))
        {
            button.up('window').close();
        }
    },
            
    cancel: function(button, eventObject)
    {
        button.up('window').close();
    },    
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.typeTree');       
        return controller;
    }
    
});