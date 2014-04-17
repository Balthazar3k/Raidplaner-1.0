<?php

/* 
 * Copyright: Balthazar3k.funpic.de 2014
 * Modue: Raidplaner 1.1
 */

require_once('include/raidplaner/database.php');

class Raidplaner {
    
    protected $db;
    
    protected $charakter;
    
    protected $permission;
    
    protected $confirm;
    
    protected $times;


    public function db(){
        if(empty($this->db)){
            $this->db = new Database();
        }
        
        return $this->db;
    }
    
    public function charakter($id = false){
        if(empty($this->charakter)){
            $this->charakter = new Charakter($this);
        } 
        
        if( $id ){
            $this->charakter->setId($id);
        } else {
            $this->charakter->setId(NULL);
        }
        
        return $this->charakter;      
    }
    
    public function permission(){
        if(empty($this->permission)){
            $this->permission = new Permission($this);
        }
        
        return $this->permission;
    }
    
    public function confirm(){
        if(empty($this->confirm)){
            $this->confirm = new Confirm($this);
        }
        
        return $this->confirm;
    }
    
    public function times(){
        if(empty($this->times)){
            $this->times = new Times($this);
        }
        
        return $this->times;
    }
    
    /**
     * change the array to a HTML Atributes string
     * 
     * @param array $attributes
     * @return string
     */
    
    public function setAttr($attributes)
    {
        if( is_Array($attributes) && count($attributes) > 0 ){
            $attr = array();
            foreach( $attributes as $key => $value){
                if( is_array($value) ){
                    $attr[] = $key . '="'.$this->setCSS($value).'"';
                } else {
                    $attr[] = $key . '="'.$value.'"';
                }
            }
            return implode(' ', $attr);
        }
    }
}

class Charakter {
    
    protected $raidplaner;
    
    protected $_id;
    
    public function __construct($object) {
        $this->raidplaner = $object;
        return $this;
    }
    
    public function setId($id){
        $this->_id = (int) $id;
    }
    
    public function save($data) {

        $status = array();
        
        //arrPrint($data); exit();
        
        $ID = $this->raidplaner->db()->select('id')
                ->from('raid_chars')
                ->where(array('user' => $_SESSION['authid'], 'id' => $this->_id))
                ->cell();
        
        if( $ID ){
            $status[] = (bool) $this->raidplaner->db()->update('raid_chars')->fields($data['charakter'])->where(array('id' => $this->_id ))->init();
            $status[] = (bool) $this->raidplaner->db()->delete('raid_zeit_charakter')->where(array('cid' => $ID ))->init();
            foreach( array_keys( $data['times']) as $timeID ){
                $status[] = (bool) $this->raidplaner->db()->insert('raid_zeit_charakter')->fields(array('zid' => $timeID, 'cid' => $ID))->init();
            }
        } else {
            $status[] = (bool) $this->raidplaner->db()->insert('raid_chars')->fields($data['charakter'])->init();
            $charakter_id = $this->raidplaner->db()->select('id')->from('raid_chars')->where(array('name' => $data['charakter']['name']))->cell();
            foreach( array_keys( $data['times']) as $timeID ){
               $status[] = (bool) $this->raidplaner->db()->insert('raid_zeit_charakter')->fields(array('zid' => $timeID, 'cid' => $charakter_id))->init();
            }
        }
        

        if(in_array( false, $status)){
            return false;
        } else {
            return true;
        }
    }

    public function delete($cid) {

        $status = array();
        $status[] = (bool) $this->raidplaner->db()->delete('raid_chars')->where(array('id' => $cid))->init();
        $status[] = (bool) $this->raidplaner->db()->delete('raid_dkp')->where(array('cid' => $cid))->init();
        $status[] = (bool) $this->raidplaner->db()->delete('raid_kalender')->where(array('cid' => $cid))->init();
        $status[] = (bool) $this->raidplaner->db()->delete('raid_anmeldung')->where(array('char' => $cid))->init();

        if(in_array( false, $status)){
            return false;
        } else {
            return true;
        }
    }
    
    public function owner($id){
        return $this->raidplaner->db()
                ->select('id')
                ->from('raid_chars')
                ->where(array('id' => $id, 'user' => $_SESSION['authid']))
                ->cell();
    }
    
    public function name(){
        return $this->raidplaner->db()
                ->select('name')
                ->from('raid_chars')
                ->where(array('id' => $this->_id))
                ->cell();
    }
    
    public function rank(){
        return $this->raidplaner->db()
                ->select('rank')
                ->from('raid_chars')
                ->where(array('id' => $this->_id))
                ->cell();
    }
    
    public function get(){
        $res = $this->raidplaner->db()
                ->select('*')
                ->from('raid_chars')
                ->where(array('id' => $this->_id))
                ->row();
        
        if( !$res ){
            return array();
        }
        
        return $res;
    }
    
    
    
    public function form($title, $pfad, $charakter = array()){
        global $allgAr;
        
        $tpl = new tpl ('raid/CHARS_EDIT_CREAT.htm');
        
        $row['title'] = $title;
        $row['pfad'] = $pfad;
        
        $row['name'] = $charakter['name'];
        $row['level'] = $charakter['level'];
        $row['rassen'] = drop_down_menu("prefix_raid_rassen" , "charakter[rassen]", $charakter['rassen'], "");
        
        $res = $this->raidplaner->db()->select('*')->from('raid_klassen')->init();
        while( $val = db_fetch_assoc($res)){ 
            
            $row['klassen'] .= $tpl->list_get('klassen', 
                array(
                    $val['id'], 
                    $val['klassen'], 
                    ($charakter['klassen'] == $val['id'] ? 'selected="selected"' : '')
                )
            );  
        }
        
        $row['spz'] = classSpecialization($charakter['klassen'], $charakter['s1'], $charakter['s2']);
        $row['skillgruppe'] = skillgruppe(1, $charakter['skillgruppe']);
        $row['warum']  = $charakter['warum'];
        $row['realm'] = $allgAr['realm'];
        
        $res = $this->raidplaner->times()->get();
        while( $result = db_fetch_assoc($res)){
            $row['times'] .= $tpl->list_get('times', array(
                $result['id'],
                $result['start'],
                $result['end']
            ));
        }

        $tpl->set_ar_out( $row, 0 );
    }
}

class Times{
    
    protected $raidplaner;
    
    public function __construct($object) {
        $this->raidplaner = $object;
        return $this;
    }
    
    public function get(){
        return $this->raidplaner->db()->select('*')->from('raid_zeit')->init();
    }
    
    public function save($data, $id = false){
        if( $id ){
            $this->raidplaner->db()->update('raid_zeit')->fields($data)->where(array('id' => $id))->init();
        } else {
            $this->raidplaner->db()->insert('raid_zeit')->fields($data)->init();
        }
    }
    
    public function delete($id){
        return $this->raidplaner->db()->delete('raid_zeit')->where(array('id' => $id))->init();
    }
}

class Confirm {
    
    protected $raidplaner;
    
    protected $_message;    
    protected $_true;
    protected $_false;
    protected $_button;
    
    public function __construct($object) {
        $this->raidplaner = $object;
        return $this;
    }
    
    public function message($message){
        $this->_message = (string) $message;
        return $this;
    }
    
    public function onTrue($url) {
        $this->_true = (string) $url;
        return $this;
    }
    
    public function onFalse($url){
        $this->_false = (string) $url;
        return $this;
    }
    
    public function html($title = '') {

        $attr = array(
            'data-true' => $this->_true,
            'data-false' => $this->_false,
        );
        
        return '
            <div id="dialog-confirm" title="'.$title.'" '. $this->raidplaner->setAttr($attr).'>
                '.$this->_message.'
            </div>
        ';
    }
}

class Permission {
    
    protected $raidplaner;
    
    protected $create;
    protected $update;
    protected $delete;
    
    public function __construct($object) {
        $this->raidplaner = $object;
        
        /* Permissions for Creating */
        $this->create = array(
            'times' => array(
                'permission' => ( $_SESSION['charrang'] >= 13 || is_admin() ), 
                'message' => 'Sie haben nicht die n&ouml;tigen Rechte um die Zeiten zu bearbeiten!'
            )
        );
        
        /* Permissions for Updateing */
        $this->update = array(
            'charakter' => array(
                'permission' => ( $_SESSION['charrang'] >= 13 || is_admin() ), 
                'message' => 'Sie haben nicht die n&ouml;tigen Rechte!'
            )
        );
        
        /* Permissions for Deleting */
        $this->delete = array(
            'charakter' => array(
                'permission' => ( $_SESSION['charrang'] >= 13 || is_admin() ), 
                'message' => 'Sie haben nicht die n&ouml;tigen Rechte!'
            ),
            'times' => array(
                'permission' => ( $_SESSION['charrang'] >= 13 || is_admin() ), 
                'message' => 'Sie haben nicht die n&ouml;tigen Rechte um die Zeiten zu L&ouml;schen!'
            )
        );
        
        return $this;
    }
    
    public function create($key, &$message = NULL) {
        $message = $this->create[$key]['message'];
        return $this->create[$key]['permission'];
    }
    
    public function update($key, &$message = NULL) {
        $message = $this->update[$key]['message'];
        return $this->update[$key]['permission'];
    }

    public function delete($key, &$message = NULL) {
        $message = $this->delete[$key]['message'];
        return $this->delete[$key]['permission'];
    }
    
}
?>