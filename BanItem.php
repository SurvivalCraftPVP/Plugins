<?php
 
/*
__PocketMine Plugin__
name=BanItem
description=You can ban the use of a item
version=1.4
apiversion=8,9,10,11
author=InusualZ
class=BanItem
*/
 
define("PLUGIN_VERSION", 1.4);

class BanItem implements Plugin{
    private $api, $config;
 
    public function __construct(ServerAPI $api, $server = false){
        $this->api  = $api;
    }
    
    public function init()
    {
    	$this->api->addHandler("player.equipment.change", array($this, 'handler'), 15);
    	$this->api->console->register("item", "[BanItem] Bans an item you don't want to use on your server", array($this, 'commands'));
    	
        $this->config = new Config($this->api->plugin->configPath($this) . "config.yml", CONFIG_YAML, array(
    		"banned-item-list" 			=> array(),
    		"plugin-version"			=> PLUGIN_VERSION,
    		"msg-on-equipment-change" 	=> "[BanItem] You are trying to use a banned item",
    		"msg-on-ban-item"			=> "[BanItem] The item/block: @name, have been banned.",
    		"msg-on-unban-item"			=> "[BanItem] The item/block: @name have been banned."
    	));

    	if($this->config->get('plugin-version') != PLUGIN_VERSION)
    	{
    		unlink($this->api->plugin->configPath($this) . "config.yml");
    		$this->config = new Config($this->api->plugin->configPath($this) . "config.yml", CONFIG_YAML, array(
				"banned-item-list" 			=> array(),
				"plugin-version"			=> PLUGIN_VERSION,
				"msg-on-equipment-change" 	=> "[BanItem] You are trying to use a banned item",
				"msg-on-ban-item"			=> "[BanItem] The item/block: @name, have been banned.",
				"msg-on-unban-item"			=> "[BanItem] The item/block: @name have been banned."
    		));
    	}
    }

    public function handler(&$data, $event)
    {
    	if($event === "player.equipment.change")
    	{
            $list = $this->config->get('banned-item-list');

    		if(in_array($data['item']->getID(), $list))
    		{
    			$msg = str_replace('@name', $data['item']->getName(), $this->config->get('msg-on-equipment-change'));
    			$data['player']->sendChat($msg);
    			return false;
    		}
            elseif(in_array($data["item"]->getName(), $list))
            {
                $msg = str_replace('@name', $data['item']->getName(), $this->config->get('msg-on-equipment-change'));
                $data['player']->sendChat($msg);
                return false;
            }
    	}
    }

    public function commands($cmd, $params)
    {
    	if($cmd == 'item')
    	{
            $c = $this->config->getAll();
            $list = $c["banned-item-list"];

    		switch (strtolower(array_shift($params))) 
            {
    			case 'ban':
    				$id = array_shift($params);
                    if(empty($id) || (is_int($id) && $id <= 0) || $id === NULL)
                    {
                        console("Usage: \item <ban|unban> <id|name>");
                        return;
                    }

    				if(!in_array($id, $list))
    				{
                        if(!is_array($list))
                            $list = array($id);
                        else
                            $list[] = $id;

    					$msg = str_replace('@name', $id, $this->config->get('msg-on-ban-item'));
    					$this->api->chat->broadcast($msg);
    				}
                    else
                    {
                        console("[BanItem] The item is alraedy banned.");
                    }

                    $c["banned-item-list"] = $list;
                    $this->config->setAll($c);
                    $this->config->save();
    			break;
    			
    			case 'unban':
    				$id = array_shift($params);
                    if(empty($id) || (is_int($id) && $id <= 0) || $id === NULL)
                    {
                        console("Usage: \item <ban|unban> <id|name>");
                        return;
                    }

    				if(in_array($id, $list))
    				{
				        $key = array_search($id, $list);
				        unset($list[$key]);

    					$msg = str_replace('@name', $id, $this->config->get('msg-on-unban-item'));
    					$this->api->chat->broadcast($msg);
    				}
                    else
                    {
                        console("[BanItem] The item is not banned.");
                    }

                    $c["banned-item-list"] = $list;
                    $this->config->setAll($c);
                    $this->config->save();
    			break;
    		}
    	}
    }

    public function __destruct(){}
}
