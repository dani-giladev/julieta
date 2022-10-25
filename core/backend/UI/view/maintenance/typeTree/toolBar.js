Ext.define('App.core.backend.UI.view.maintenance.typeTree.toolBar', {
    extend: 'Ext.toolbar.Toolbar',
    
    alias: 'widget.maintenance_typetree_toolbar',
        
    explotation: 'Maintenance tree (toolbar view)',

    region: 'north',
                
    border: true,
    frame: false,
    
//    ui: 'footer',
    
    config: null,
    
    initComponent: function() {
        
        var me = this;
        
        this.itemId = 'maintenance_typetree_toolbar' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        this.title = '';
        
        this.items = 
        [
            {
                text: me.trans('new'),
                iconCls: 'x-fa fa-file-o',
                handler: me.newNode,
                disabled: !me.config.permissions.update
            },
            {
                text: me.trans('edit'),
                iconCls: 'x-fa fa-edit',
                handler: me.editNode,
                disabled: !me.config.permissions.update
            },   
            {
                text: me.trans('delete'),
                iconCls: 'x-fa fa-remove',
                disabled: !me.config.permissions.delete,
                hidden: !me.config.enable_deletion,
                handler: me.deleteNode
            },             
            {
                text: me.trans('undo'),
                iconCls: 'x-fa fa-undo',
                handler: me.refresh,
                disabled: !me.config.permissions.update
            },     
            {
                itemId: 'refresh_button_form',
                text: me.trans('refresh'),
                iconCls: 'x-fa fa-refresh',
                handler: me.refresh
            },        
            { xtype: 'tbfill' },
            {
                text: me.trans('save'),
                iconCls: 'x-fa fa-save',
                disabled: !me.config.permissions.update,
                handler: me.save
            },      
            {
                text: me.trans('save_and_publish'),
                iconCls: 'x-fa fa-flag-checkered',
                disabled: (!me.config.permissions.update || !me.config.permissions.publish),
                hidden: !me.config.enable_publication,
                handler: me.saveAndPublish
            },        
            {
                text: me.trans('publish'),
                iconCls: 'x-fa fa-flag-o',
                disabled: !me.config.permissions.publish,
                hidden: !me.config.enable_publication,
                handler: me.publish
            }
        ];
            
        this.callParent(arguments);
    },
            
    save: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().save(me.config, false);
    },
            
    saveAndPublish: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().save(me.config, true);
    },
            
    publish: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().publish(me.config);
    },
            
    newNode: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().showEditForm(me.config, true);
    },
            
    editNode: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().showEditForm(me.config, false);
    },
            
    deleteNode: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().deleteNode(me.config);
    },
            
    refresh: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().refreshTree(me.config);
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