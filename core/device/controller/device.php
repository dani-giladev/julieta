<?php

namespace core\device\controller;

// Libs
use Detection\MobileDetect;

/**
 * Device controller
 *
 * @author Dani Gilabert
 * 
 */
class device
{
    private static $_initialized = false;
    private static $_mobiledetect = null;
    
    private static $_is_mobile = null;
    private static $_is_tablet = null;
    private static $_is_touch_device = null;
    private static $_is_mobile_version = null;
    
    public static function init()
    {
        if (self::$_initialized)
        {
            return;
        }
        
        self::$_mobiledetect = new MobileDetect();
        self::$_initialized = true;
    }
    
    public static function isMobile()
    {
        if (!is_null(self::$_is_mobile)) return self::$_is_mobile;
        self::init();
        $ret = self::$_mobiledetect->isMobile();
        self::$_is_mobile = $ret;
        return $ret;        
    }
    
    public static function isTablet()
    {
        if (!is_null(self::$_is_tablet)) return self::$_is_tablet;
        self::init();
        $ret = self::$_mobiledetect->isTablet();
        self::$_is_tablet = $ret;
        return $ret;        
    }
    
    public static function isTouchDevice()
    {
        if (!is_null(self::$_is_touch_device)) return self::$_is_touch_device;
        self::init();
        $ret = self::isMobile() || self::isTablet();
        self::$_is_touch_device = $ret;
        return $ret;        
    }
    
    public static function isMobileVersion()
    {
//        return true;
        if (!is_null(self::$_is_mobile_version)) return self::$_is_mobile_version;
        self::init();
        $ret = self::isMobile() || self::isTablet();
        self::$_is_mobile_version = $ret;
        return $ret;        
    }
    
    public static function setIsMobileVersion($value)
    {
        self::$_is_mobile_version = $value;
    }
 
}