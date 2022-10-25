Ext.define('App.core.backend.UI.view.fileManager.tree', {
    extend: 'Ext.tree.Panel',
    alias: 'widget.filemanager_tree',
    
    explotation: 'File manager tree view',
    
    region: 'center',
    border: false,
    frame: false,
    scrollable: true,
    hideHeaders: true,
    
    config: {},
    
    initComponent: function()
    {
        var me = this;
        
        me.itemId = me.config.itemId + '_filemanager_tree';
        
        me.title = '';
        me.useArrows = true;
//        me.collapsed = false;
//        me.floatable = false;
        me.rootVisible = true;
        
//        me.constraintInsets = '0 0 0 10';
        
//        me.root = {
//            text: (Ext.isEmpty(me.config.baseNode)? '/' : me.config.baseNode),
//            expanded: true
//        };
        
        me.store = Ext.create('Ext.data.TreeStore', {
            autoLoad: true,
            root:
            {
                text: (Ext.isEmpty(me.config.baseNode)? '/' : me.config.baseNode),
                expanded: true,     
                
                draggable: false, // disable root node dragging
                nodeType: 'async',
                autoSync: true  
            },
            proxy: {
                type: 'ajax',
	        url : 'index.php',
                extraParams: {
                    controller: 'core\\backend\\controller\\fileManager',
                    method: 'getDir',
                    base_node: me.config.baseNode
                }
	    }
	});       
        
        me.columns =
        [
            {
                xtype: 'treecolumn',
                renderer: me.formatName,
                align:'left',
                flex: 1
            }
        ];
        
        me.store.on('load', function(this_store, records, successful, eOpts)
        {
            me.getViewController().loadGridStore(me.config, 'root');
        }, this, {single: true});         
        
        me.listeners = {
            itemclick: {
                fn: function(view, record, item, index, event)
                {
                    me.getViewController().loadGridStore(me.config, record.data.id);
                }
            }
        };
        
        me.callParent(arguments);
        
        me.store.on('load', this.onLoad, this, {single: true});
    },

    formatName: function(value, metadata, record, rowIndex, colIndex, store)
    {
        return record.data.text;
    },
    
    onLoad: function(this_store, node, records, successful, eOpts)
    {
        var node = this.getRootNode();
        node.expandChildren(true);
        this.getSelectionModel().select(node);        
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.fileManager');       
        return controller;
    }
});