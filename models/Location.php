<?php
require_once 'LocationTable.php';
/**
 * Location
 * @package: Omeka
 */
class Location extends Omeka_Record
{
    public $item_id;
    public $latitude;
    public $longitude;
    public $zoom_level;
    public $map_type;
    public $address;
    
    protected function _validate()
    {
        if (empty($this->item_id)) {
            $this->addError('item_id', 'Location requires an item id.');
        }
    }
    
    
    
    /*
    Override save() in Record
    */
    
        /**
     * Save the record.
     * 
     * If the record does not validate, nothing will happen.  If the record is 
     * not yet persisted, it will be inserted into the database.  If it is, then
     * the existing database row(s) will be updated.
     * 
     * @return boolean Whether or not the save was successful.
     */
    public function save()
    {    
        if ($this->_locked) {
            throw new Omeka_Record_Exception('Cannot save a locked record!');
        }
        
        if (!$this->isValid()) {
            return false;
        }
        
        $was_inserted = !$this->exists();
        
        // Some callbacks
        if ($was_inserted) {
            $this->runCallbacks('beforeInsert');
        } else {
            $this->runCallbacks('beforeUpdate');
        }
        
        $this->runCallbacks('beforeSave');
        
        // Only try to save columns in the $data that are actually defined
        // columns for the model
        $data_to_save = $this->toArray();
        
        
        
        
        /* here's what we need to override:
        
            $insert_id = $this->getDb()->insert(get_class($this), $data_to_save);
            
        see http://omeka.org/phpdoc/1.0/Omeka/Omeka_Db.html#insert : this will look like 
        
            INSERT INTO table (field, field2, field3, ...) VALUES (?, ?, ?, ...) 
               ON DUPLICATE KEY UPDATE field = ?, field2 = ?, ...
            
        But we need insert everything but the item_id into Location, getting a location_id back if 
        it already exists; then insert the item_id into Location_To_Item
        
        array:
         geolocation[latitude] = 52.519171
         geolocation[longitude] = 13.406091199999992
         geolocation[zoom_level] = 5
         geolocation[map_type] = Google Maps v3.x
         geolocation[address] = Berlin, Germany
        */
        
         // add address_hash
         $data_to_save['address_hash'] = sha1($data_to_save['address']);
         
         // remove item_id
         $item_id = $data_to_save['item_id'];
         unset($data_to_save['item_id']);
         
         // insert Location

         $insert_id = $this->getDb()->insert(get_class($this), $data_to_save);
         
         // insert Location_To_Item
        
        
        
        
        
        if ($was_inserted && (empty($insert_id) || !is_numeric($insert_id))) {
            throw new Omeka_Record_Exception("LAST_INSERT_ID() did not return a numeric ID when saving the record.");
        }
        $this->id = $insert_id;
        
        if ($was_inserted) {
            // Run the local afterInsert hook, the modules afterInsert hook, then
            // the plugins' insert_record hook
            $this->runCallbacks('afterInsert');
        } else {
            $this->runCallbacks('afterUpdate');
        }
        
        $this->runCallbacks('afterSave');
               
        return true;
    }
    
    
}