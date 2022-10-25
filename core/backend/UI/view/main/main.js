Ext.define('App.core.backend.UI.view.main.main', {
    extend: 'Ext.panel.Panel',  
    xtype: 'main',  

    id: 'main_viewport',

    requires: [
        'Ext.plugin.Viewport',
        'App.core.backend.UI.controller.main'
    ],

    plugins: ['viewport'],
    
    layout: 'border',
    border: false,
    frame: false,
    title: '',
    
    initComponent: function()
    {
        var me = this;
        
        var title = 
            app_title + ' ' + app_version + 
            '<div style="font-size:10px;">' + 
                logged_full_name_user + 
            '</div>' + 
            '';
        
        me.items = 
        [
            {
                xtype: 'panel',
                id: 'main-menu-wrapper',
                region: 'west',
                title: title,
                width: 200,
                height: '100%',
                collapsible: true,
                collapsed: false,
                layout: 'border',     
                border: true,
                frame: false,
                items:
                [
                    {
                        xtype: 'panel',
                        id: 'main-menu',
                        region: 'center',
                        layout: 'vbox',
                        scrollable: true,
                        title: '',
    //                    bodyStyle: {
    //                        'background-color': 'gray'
    //                    },
                        defaults: {
                            xtype: 'button',
                            width: '100%',
                            height: 70,
                            cls: "main-menu-btn",
                            //overCls : 'main-menu-btn-over',
                            //pressedCls : 'main-menu-btn-pressed',                

                            //needs to be true to have the pressed cls show
                            toggleGroup : 'main-menu',
                            enableToggle : true,
                            allowDepress:false
                        },
                        items:
                        [

                        ]
                    },
                    {
                        xtype: 'panel',
                        region: 'south',
                        title: '',
                        height: 75,
                        defaults: {
                            xtype: 'button',
                            width: '100%',
                            height: 75,
                            cls: "logout-btn",
                            overCls : 'logout-btn-over'
                        },
                        items:
                        [
                            {
                                text: 'Logout',
                                iconCls: 'fa fa-power-off',
                                listeners: {
                                    click: function(element, e) {
                                        me.getViewController().logout(element, e);                                        
                                    }
                                }
                            }
                        ]
                    }                
                ]
            },
            {
                xtype: 'panel',
                id: 'main-module-container',
                region: 'center',
                layout: 'fit',
                title: '',
                width: '100%',
                height: '100%',
                _current_module_id: '',
                bodyPadding: 10,
                items:
                [

                ]
            }
        ];

        me.callParent(arguments);
    },
    
    onRender: function(me, options)
    {
        var me = this;
        
        me.getViewController().initModules();
        
        this.callParent(arguments);
    },
            
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.main');       
        return controller;
    }
});