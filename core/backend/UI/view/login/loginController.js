Ext.define('App.core.backend.UI.view.login.loginController', {
    extend: 'App.core.backend.UI.view.viewController',
    
    alias: 'controller.login',

    init: function() 
    {
        var me = this;
        me.control({
            '#login_user': 
            {
                specialkey: me.onSpecialKey
            },            
            '#login_password': 
            {
                specialkey: me.onSpecialKey
            },
            '#login_lang': 
            {
                specialkey: me.onSpecialKey
            },
            '#login_rememberme': 
            {
                specialkey: me.onSpecialKey
            },
            '#submit_button': 
            {
                click: function(){
                    me.submitForm();
                }
            }
        }); 
    },

    onSpecialKey: function(field, e) 
    {
        var me = this;
        
        if ((e.getKey() === e.ENTER || e.getKey() === e.TAB) && field.isValid()) 
        {
            e.stopEvent();

            if (e.getKey() === e.TAB)
            {
                var nextField = me.getNextField(field);
                nextField && nextField.focus();                 
            }
            else
            {
                this.jumpToBlankField();
            }
        }        
    },
    
    getNextField: function(field)
    {
        var me = this,
            view = me.getView(),
            form = view.down('form');
    
        var fields = form.query('textfield, numberfield, combo');
        if (!fields)
        {
            return false;
        }   
        
        var currentFieldIdx = fields.indexOf(field);    
        if(currentFieldIdx <= -1) 
        {
            return false;
        }
        
        // Get the next form field
        var nextField, i=1;
        while (true) {
            //console.log(i);
            nextField = fields[currentFieldIdx + i];
            if (!nextField)
            {
                //console.log(nextField);
                return false;
            }
            if (!nextField.isHidden() && !nextField.isDisabled())
            {
                //console.log(nextField);
                return nextField;
            }
            i += 1;
        };

        return false;
    },
    
    jumpToBlankField: function()
    {
        var me = this,
            view = me.getView(),
            this_form = view.down('form');
        var user, password, lang;
        
        // Set focus in the blank field
        user = this_form.down('#login_user');
        if (user.getValue() === '')
        {
            user.focus();
            return;
        }
        password = this_form.down('#login_password');
        if (password.getValue() === '')
        {
            password.focus();
            return;
        }   
//        lang = this_form.down('#login_lang');
//        if (lang.getValue() === '')
//        {
//            lang.focus();
//            return;
//        }  
        
        this.submitForm();        
    },
            
    submitForm: function()
    {
        var me = this,
            view = me.getView(),
            this_form = view.down('form');
        var this_window = view.up('window');
        
        if (this_form.isValid())
        {
            this_form.submit(
            {
                type: 'ajax',
                url: 'index.php',
                method: 'POST',
                params: {
                    controller: 'core\\backend\\controller\\login', 
                    method: 'checkLogin'
                },
                waitMsg: view.trans('validating'),
                success: function(form, data)
                {
                    var obj = Ext.JSON.decode(data.response.responseText);
                    if (obj.data.result.success)
                    {
                        var logged_lang_saved = logged_lang;
                                
                        // Set global vars
                        App.app.getController('App.core.backend.UI.controller.init').setGlobalVars(obj.data.result);
                
                        // Close window and show the main viewport
                        this_window.close();
                        
                        var core_lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
                        var load_translations = (logged_lang_saved !== logged_lang || !core_lang_store);
                        //console.log(load_translations);
                        App.app.getController('App.core.backend.UI.controller.init').showMainViewPort(load_translations);
                    }
                    else
                    {
                        Ext.MessageBox.show({
                           title: view.trans('login_failed_title'),
                           msg: obj.data.result.msg,
                           buttons: Ext.MessageBox.OK,
                           icon: Ext.MessageBox.WARNING
                        });
                    }
                },
                failure: function(form, data)
                {
                    var obj = Ext.JSON.decode(data.response.responseText);
                    Ext.MessageBox.show({
                       title: view.trans('login_failed_title'),
                       msg: obj.data.result.msg,
                       buttons: Ext.MessageBox.OK,
                       icon: Ext.MessageBox.WARNING
                    });
                }
            });            
        }        

    }

});