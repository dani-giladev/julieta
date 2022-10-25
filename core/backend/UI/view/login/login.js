Ext.define('App.core.backend.UI.view.login.login' ,{
    extend: 'Ext.panel.Panel', 
    xtype: 'login',
    controller: 'login',

    alias: 'widget.login_window',
    
    requires: [
        'App.core.backend.UI.view.login.loginController',
        'App.core.backend.UI.store.loginLanguages',
        'Ext.form.Panel'
    ],
    
    fullscreen:true,
    width: '100%',
    height: '100%',
    closable: false,
    resizable: false,    
    header: false,
    frame: false,
    border: false,
    
    backgroundColor: '#E5EEFF', //'#FCFDFF',
    labelColor: 'gray',
    
    initComponent: function() {
        var me = this;
        
        me.title = app_title + " " + app_version;
            
        me.bodyStyle = {
            'background-color': me.backgroundColor
        };
    
        me.items = 
        [
            {
                xtype: 'panel',
                width: '100%',
                bodyStyle: {
                    'text-align': 'center',
                    'background-color': me.backgroundColor
                },                
                renderTo: Ext.getBody(),
                items: 
                [
                    {
                        xtype: 'image',
                        src: app_path_logo,
                        width: app_width_logo,
                        height: app_height_logo,
                        margin: '40 0 0 0',
                        renderTo: Ext.getBody()            
                    }
                ]
            },
            {
                xtype: 'panel',
                width: '100%',
                bodyStyle: {
                    'background-color': me.backgroundColor
                },
                layout: {
                    type: 'vbox',
                    align: 'center',
                    pack: 'center'
                },     
                renderTo: Ext.getBody(),
                items:
                [
                    {
                        xtype: 'form',
                        id: 'login_form',
                        title: '',
                        frame: false,
                        border: false,
                        width: 300,
                        margin: '10 0 0 0',
                        bodyStyle: {
                            'background-color': me.backgroundColor
                        },
                        loginfailed: app_title + ' ' + me.trans('login_failed_title'),
                        items: 
                        [
                            {
                                xtype: 'label',                                       
                                text: me.trans('user'),
                                style: 'color:' + me.labelColor + '; font-weight:bold'
                            },                                   
                            {
                                xtype: 'textfield',
                                id: 'login_user',
                                name: 'user',
                                fieldLabel: '',
                                allowBlank: false,
                                blankText: me.trans('this_field_is_required'),                                
                                anchor: '100%',
                                margin: '5 0 10 0'
                            },
                            {
                                xtype: 'label',
                                text: me.trans('password'),
                                style: 'color:' + me.labelColor + '; font-weight:bold'
                            },                                     
                            {
                                xtype: 'textfield',
                                id: 'login_password',
                                name: 'password',
                                inputType: 'password',
                                fieldLabel: '',
                                allowBlank: false,
                                blankText: me.trans('this_field_is_required'),
                                anchor: '100%',
                                margin: '5 0 10 0'
                            },
                            {
                                xtype: 'label',
                                text: me.trans('language'),
                                style: 'color:' + me.labelColor + '; font-weight:bold'
                            },
                            {
                                xtype: 'combo',
                                id: 'login_lang',
                                name: 'lang',
                                fieldLabel: '',
                                displayField: 'name',
                                valueField: 'code',
                                store: Ext.create('App.core.backend.UI.store.loginLanguages'),
                                submitValue: true,
                                editable: false,
                                emptyText: me.trans('select_language'),
                                allowBlank: false,
                                blankText: me.trans('this_field_is_required'),
                                anchor: '100%',
                                margin: '5 0 30 0'
                            },
                            {
                                xtype: 'panel',
                                bodyStyle: {
                                    'background-color': me.backgroundColor
                                },                                
                                layout: {
                                    type: 'hbox',
                                    align: 'center',
                                    //pack: 'left',
                                    pack: 'center'
                                },                        
                                renderTo: Ext.getBody(),
                                items:
                                [
                                    {
                                        xtype: 'button',
                                        id: 'submit_button',                                        
                                        text: me.trans('sign_in'),
                                        formBind: true,
                                        disabled: true,                                
                                        width: 150,
                                        height: 40
                                    }/*,                                            
                                    {
                                        xtype: 'checkbox',
                                        id: 'login_rememberme',
                                        checked: true,
                                        boxLabel: me.trans('remember_me'),
                                        name: 'rememberme',
                                        inputValue: 'true',
                                        margin: '10 0 0 10'
                                    }*/
                                ]
                            }                    
                        ]           
                    }
                ]
            }            
        ];

        me.callParent(arguments);
        this.on('afterrender', this.onAfterrender, this);  
    },
    
    onRender: function(this_window, options)
    {
        var login_lang = Ext.getCmp('login_lang');
        login_lang.getStore().on('load', function(this_store, records, successful, eOpts)
        {
            login_lang.setValue(logged_lang);
            Ext.getCmp('login_user').setValue(logged_user);
            
        }, this, new Object({single: true}));
        login_lang.getStore().load();
        
        this.callParent(arguments);
    },
    
    onAfterrender: function(form, eOpts)
    {
        // Set focus in login field
        var task = new Ext.util.DelayedTask(function(){
            Ext.getCmp('login_user').focus();
        });        
        task.delay(100);
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    }
});