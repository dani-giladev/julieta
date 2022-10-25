Ext.define('App.core.backend.UI.view.viewController' ,{
    extend: 'Ext.app.ViewController',
    alias: 'controller.viewController',
 
    doInit: function(view)
    {

    },
    
    getSize: function()
    { 
        var ret = {};
        var w = window,
            d = document,
            e = d.documentElement,
            g = d.getElementsByTagName('rows')[0],
            x = w.innerWidth || e.clientWidth || g.clientWidth,
            y = w.innerHeight|| e.clientHeight|| g.clientHeight;
        ret.width = x;
        ret.height = y;
        return ret;
    }
    
});