<?php
/**
 * Classes gérant les modèles sql du crud
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWCrud;

use BFWSql\Modeles;
use \Exception;

/**
 * Modeles unique pour toutes les tables géré par le crud
 * @package bfw-crud
 */
class CrudModeles extends Modeles
{
    /**
     * @var $_columnID : Colonne représentant l'id
     */
    protected $_columnID = '';
    
    /**
     * Setter sur la table à utiliser
     * 
     * @param string $table : Le nom de la table
     * 
     * @return bool
     */
    public function setTable($table)
    {
        if(!is_string($table)) {return false;}
        
        $this->_name = $table;
        return true;
    }
    
    /**
     * Ajoute des données dans la table
     * 
     * @param array $data : Les données à ajouté. La clé sur l'array est le nom du champs.
     * 
     * @throws \Exception
     * 
     * @return bool
     */
    public function add($data)
    {
        $dataVerif = array();
        
        foreach($this->_map as $key => $info)
        {
            if($info['type'] == 'string') {$data[$key] = addslashes($data[$key]);}
            
            $dataVerif[] = array(
                'type' => $info['type'],
                'data' => ((isset($data[$key])) ? $data[$key] : $info['default'])
            );
        }
        
        $verif = verifTypeData($dataVerif);
        
        if($verif)
        {
            try {$exec = $this->insert($this->_name, $data)->execute();}
            catch(Exception $e)
            {
                echo '<pre>'.$e.'</pre><br/><br/>';
                return false;
            }
            
            if(!$exec)
            {
                throw new Exception('Erreur durant l\'ajout à la bdd de la donnée : '.print_r($data, true));
                return false;
            }
        }
        else
        {
            throw new Exception('Les paramètres données sont incorrectes.');
            return false;
        }
        
        return true;
    }

    /**
     * Récupère toutes les données de la table sans aucun critère de recherche ou d'ordre.
     * 
     * @throws \Exception
     * 
     * @return array|bool : Array contenant les informations. False si une erreur survient.
     */
    public function getAll()
    {
        $columns = $this->getColumns();
        
        $req = $this->select()->from($this->_name, $columns);
        
        try {$res = $req->fetchAll();}
        catch(Exception $e)
        {
            echo '<pre>'.$e.'</pre><br/><br/>';
            return false;
        }
        
            if($res) {return $res;}
        elseif(!$res && $req->nb_result() === 0) {return array();}
        else
        {
            throw new Exception('Erreur durant la récupération des données.');
            return false;
        }
    }

    /**
     * Retourne les infos sur un élémént
     * 
     * @param mixed  $id     : La valeur pour le champ indiqué par $this->_columnID
     * @param string $format : Le format de retour. soit "array", soit "object"
     * 
     * @throws \Exception
     * 
     * @return array|bool : Array contenant les informations. False si une erreur survient.
     */
    public function getForID($id, $format='array')
    {
        if(empty($this->_id)) {throw new Exception('La colonne représentant l\'id n\'est pas indiqué.');}
        
        $columns = $this->getColumns();
        
        $req = $this->select($format)->from($this->_name, $columns)->where($this->_id.'=:id', array(':id' => $id));
        
        try {$res = $req->fetchRow();}
        catch(Exception $e)
        {
            echo '<pre>'.$e.'</pre><br/><br/>';
            return false;
        }
        
            if($res) {return $res;}
        elseif(!$res && $req->nb_result() === 0) {return array();}
        else
        {
            throw new Exception('Erreur durant la récupération des données.');
            return false;
        }
    }
    
    /**
     * Retourne toutes les colonnes d'une table
     * 
     * @return bool|array : false si erreur, liste des columns sinon
     */
    public function getColumns()
    {
        $columns = array();
        $query   = 'DESCRIBE '.$this->_name;
        
        $req = $this->query($query);
        
        try {$res = $req->fetchAll();}
        catch(Exception $e)
        {
            echo '<pre>'.$e.'</pre><br/><br/>';
            return false;
        }
        
        if($res)
        {
            foreach($res as $datas)
            {
                $columns[] = $datas['Field'];
            }
            
            return $columns;
        }
        elseif(!$res && $req->nb_result() === 0) {return $columns;}
        else
        {
            throw new Exception('Erreur durant la récupération des données.');
            return false;
        }
    }
    
    /**
     * Retourne le nom de la colonne utilisé comme clé primaire
     * 
     * @return string|bool : false si erreur ou pas de résultat
     */
    public function getPrimaryColumnName()
    {
        $query = 'SHOW COLUMNS FROM '.$this->_name.' WHERE `Key`="PRI"';
        $req   = $this->query($query);
        
        try {$res = $req->fetch();}
        catch(Exception $e)
        {
            echo '<pre>'.$e.'</pre><br/><br/>';
            return false;
        }
        
            if($res) {return $res['Field'];}
        elseif(!$res && $req->nb_result() === 0) {return false;}
        else
        {
            throw new Exception('Erreur durant la récupération des données.');
            return false;
        }
    }
    
    public function getDatas($sqlQuery)
    {
        try {$res = $sqlQuery->fetchAll();}
        catch(Exception $e)
        {
            echo '<pre>'.$e.'</pre><br/><br/>';
            return false;
        }
        
            if($res) {return $res;}
        elseif(!$res && $req->nb_result() === 0) {return array();}
        else
        {
            throw new Exception('Erreur durant la récupération des données.');
            return false;
        }
    }
}
