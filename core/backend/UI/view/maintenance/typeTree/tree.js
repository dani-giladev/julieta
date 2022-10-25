Ext.define('App.core.backend.UI.view.maintenance.typeTree.tree', {
    extend: 'Ext.tree.Panel',
    
    alias: 'widget.maintenance_typetree_tree',
    
    explotation: 'Maintenance tree (tree view)',

    region: 'center',

    border: false,
    frame: false,
    autoScroll: true,
    
    config: null,
            
    initComponent: function()
    {    
        var me = this;
        
        me.itemId = 'maintenance_typetree_tree' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        me.xtype = 'tree-grid';
        me.title = '';
        //me.useArrows = true;
        me.rootVisible = false;       
        
        me.viewConfig = {
            plugins: {
                ptype: 'treeviewdragdrop'
            }//,
//            listeners: {       
//                drop: function (node, data, overModel, dropPosition) {         
//                      console.log(data);
//                }        
//            }   
        };
        
        // The ajax params
        var params = {
            controller: 'core\\backend\\controller\\maintenance\\typeTree', 
            method: 'getTree',
            module_id: me.config.module_id,
            model_id: me.config.model.id
        };    
        
        me.store = Ext.create('Ext.data.TreeStore',
        {
            autoLoad: true,
            root:
            {
                text: '/',
                expanded: true,
                loaded: true,
                draggable: false
            },
            proxy:
            {
                type: 'ajax',
                url : 'index.php',
                extraParams: params
            }
        }); 
        
        me.columns =
        [
            {
                xtype: 'treecolumn', //this is so we know which column will show the tree
                text: me.trans('tree'),
                renderer: me.formatName,
                align:'left',
                flex: 1
            },
            {
                text: me.trans('available'),
                renderer: me.formatAvailable,
                align: 'center',
                width: 200
            }
        ];
        
        me.callParent(arguments);
        
        me.store.on('load', this.onLoad, this, {single: true});
//        me.on('itemclick', me.onClick, this);
        me.on('itemdblclick', me.onDblClick, this);        
    },
    
    onLoad: function(this_store, node, records, successful, eOpts)
    {
        //console.log(records);
        if(records.length === 0)
        {
            return;
        }
        
        var root_node = this.getRootNode();
        var main_record_node = root_node.getChildAt(0);
        this.getSelectionModel().select(main_record_node);        
    },
    
    onClick: function(this_tree, record, item, index, e, eOpts)
    {
        
    },
    
    onDblClick: function(this_tree, record, item, index, e, eOpts)
    {
        var me = this;
        me.getViewController().showEditForm(me.config, false);
    },
            
    formatBoolean: function(value)
    {
        return Ext.String.format('<img src="resources/ico/'+(value ? 'true' : 'false')+'.png" />');
    },

    formatName: function(value, metadata, record, rowIndex, colIndex, store)
    {
        //var code = '<font color="silver"> (' + record.raw._data.code + ')</font>';
        var code = '';
        var name = record.data.text + code;
        return name;
    },

    formatAvailable: function(value, metadata, record, rowIndex, colIndex, store)
    {
        var available = record.raw._data.available;
        return Ext.String.format('<img src="resources/ico/'+(available ? 'true' : 'false')+'.png" />');
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