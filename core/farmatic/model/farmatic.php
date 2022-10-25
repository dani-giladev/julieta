<?php

namespace core\farmatic\model;

// Controllers
use core\config\controller\config as config;

// Models
use core\model\controller\mssql;

/**
 * Model for the Farmatic interface
 *
 * @author Dani Gilabert
 * 
 */
class farmatic extends mssql
{
    public function __construct()
    {
        $conn_params = config::getConfigParam(array("application", "farmatic"))->value;

        $this->connect($conn_params);
    }  
    
    public function getArticleData($article_code)
    {
        $sql = "SELECT TOP 1 ";
        $sql .= "Articu.IdArticu, Articu.Descripcion, Articu.Pvp, Articu.Puc, Articu.Pmc, Articu.StockActual, Articu.ActualizaStock, Articu.OnLine, ";
        $sql .= "Sinonimo.Sinonimo ";
        $sql .= "FROM Articu LEFT JOIN ";
        $sql .= "Sinonimo ON Articu.IdArticu = Sinonimo.IdArticu ";
        $sql .= "WHERE Articu.IdArticu=".$article_code." ";
        $sql .= "ORDER BY Sinonimo.Sinonimo DESC";
        
        return $this->execute($sql);
    } 
    
    public function getStock($article_code)
    {
        $sql = "SELECT ";
        $sql .= "StockActual, ActualizaStock ";
        $sql .= "FROM Articu ";
        $sql .= "WHERE IdArticu=".$article_code;
        
        return $this->execute($sql);
    } 
    
    public function exist($article_code)
    {
        $sql = "SELECT COUNT(1) ";
        $sql .= "FROM Articu ";
        $sql .= "WHERE IdArticu=".$article_code;
        
        return $this->execute($sql);
    } 
    
    public function markOffAsOnlineOrOffline($article_code, $online)
    {
        $sql = "UPDATE Articu ";
        $sql .= "SET OnLine=".(($online)? '1' : '0')." ";
        $sql .= "WHERE IdArticu=".$article_code;
        
        return $this->execute($sql);
    }
    
    public function executeQuery($sql)
    {
        return $this->execute($sql);
    }
    
    public function getChangesInArticleCodes($date_time)
    {
        $sql = "SELECT ";
        $sql .= "CodigoOld, CodigoNew, FechaHora ";
        $sql .= "FROM ChgCodigoArt ";
        $sql .= "WHERE FechaHora>'".$date_time."' ";
        $sql .= "ORDER BY FechaHora ASC";
        
        return $this->execute($sql);
    } 
    
}