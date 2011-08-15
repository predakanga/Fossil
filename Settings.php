<?php

namespace Fossil;

/**
 * Description of Settings
 *
 * @author lachlan
 * @F:Object("Settings")
 */
class Settings {
    private $store;
    
    public function __construct() {
        $this->store = array();
        $this->store['Fossil'] = yaml_parse_file("settings.yml");
    }
    
    public function get($section, $setting, $default = null) {
        if(isset($this->store[$section]))
            if(isset($this->store[$section][$setting]))
                return $this->store[$section][$setting];
        return $default;
    }
    
    public function set($section, $setting, $value) {
        if(!isset($this->store[$section]))
            $this->store[$section][$setting] = $value;
        // If it's a Fossil setting, store it
        if($section == "Fossil")
            file_put_contents("settings.yml", yaml_emit($this->store['Fossil']));
    }
}

?>
