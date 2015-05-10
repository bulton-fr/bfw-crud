<?php
/**
 * Classes gérant la base du crud
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWCrud;

use \Exception;

/**
 * Permet de gérer le crud
 * @package bfw-crud
 */
class Crud
{
    /**
     * @var $_kernel L'instance du Kernel
     */
    protected $_kernel;
    
    /**
     * @var $sql : L'instance du modèles sql pour le crud
     */
    protected $sql = null;
    
    /**
     * @var string $table : La table à utiliser
     */
    protected $table = '';
    
    /**
     * @var array $relations : Les relations entre les tables
     */
    protected $relation = array();
    
    /**
     * @var array $columns : Les colonnes à afficher
     */
    protected $columns = array();
    
    /**
     * @var array $datas : Les données récupérer depuis sql
     */
    protected $datas = array();
    
    
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->_kernel = getKernel();
        
        $this->sql = new CrudModeles;
        
        global $bd_enabled;
        if($bd_enabled != true)
        {
            throw new Exception('L\'accès SQL doit être autorisé.', E_USER_ERROR);
        }
    }
    
    /**
     * Permet de définir la table à utiliser
     * 
     * @param string $table : Le nom de la table
     * 
     * @return bool
     */
    public function setTable($table)
    {
        if(!is_string($table)) {return false;}
        
        $this->table = $table;
        return true;
    }
    
    /**
     * Permet d'ajouter une relation entre deux tables
     * 
     * @param string $tableLeft
     * @param string $colJoinLeft
     * @param string $tableRight
     * @param string $colJoinRight
     * 
     * @return void
     */
    public function addRelation($tableLeft, $colJoinLeft, $tableRight, $colJoinRight)
    {
        $this->relation[] = array(
            't_left'       => $tableLeft,
            't_right'      => $tableRight,
            'c_join_left'  => $colJoinLeft,
            'c_join_right' => $colJoinRight
        );
    }
    
    /**
     * Permet de vouloir récupérer toutes les colonnes afin de toutes les afficher
     * 
     * @return bool
     */
    public function setDisplayAllColumns()
    {
        if($this->table == '') {return false;}
        
        $columns = $this->sql->getColumns();
        $primary = $this->sql->getPrimaryColumnName();
        
        foreach($columns as $colName)
        {
            $param = array();
            
            if($colName === $primary) {$param['disabled'] = true;}
            
            $this->setDisplayColumn($colName, $param);
        }
        
        return true;
    }
    
    /**
     * Permet de récupérer une colonne en particulier avec certaines options
     * 
     * @param string $colName
     * @param array  $params
     * 
     * @return bool
     */
    public function setDisplayColumn($colName, $params=array())
    {
        if($this->table == '') {return false;}
        
        $disabled = false;
        if(isset($params['disabled'])) {$disabled = $params['disabled'];}
        
        $this->columns[] = array(
            'name'     => $colName,
            'disabled' => $disabled
        );
        
        return true;
    }
    
    /**
     * Permet de récupérer toutes les données
     * 
     * @param string $order
     * @param array  $limit
     * 
     * @return array|bool
     */
    public function generate($order='', $limit=array())
    {
        if($this->table == '' || $this->columns === array())
        {
            return false;
        }
        
        $columnsName = array();
        foreach($this->columns as $colInfo)
        {
            $columnsName[] = $colInfo['name'];
        }
        
        $query = $this->sql->select()
                           ->from($this->table, $columnsName);
        
        if($order !== '')      {$query->order($order);}
        if($limit !== array()) {$query->limit($limit);}
        
        if($this->relation !== array())
        {
            foreach($this->relation as $relation)
            {
                $on = $relation['t_left'].'.'.$relation['c_join_left'].'='
                      .$relation['t_right'].'.'.$relation['c_join_right'];
                      
                $query->joinLeft($relation['t_right'], $on, array($relation['c_join_right']));
            }
        }
        
        $datas = $this->sql->getDatas($query);
        return $datas;
    }
}
