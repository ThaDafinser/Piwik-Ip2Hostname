<?php
namespace Piwik\Plugins\Ip2Hostname;

use Piwik\Plugin;
use Piwik\Common;
use Piwik\Db;
use Piwik\IP;

class Ip2Hostname extends Plugin
{

    public function getListHooksRegistered()
    {
        return array(
            'Tracker.newVisitorInformation' => 'logIp2Hostname'
        );
    }

    public function install()
    {
        return;
        
        $query = "
            ALTER IGNORE 
            TABLE `" . Common::prefixTable('log_visit') . "` 
                ADD `location_hostname` VARCHAR(255) NULL
        ";
        Db::exec($query);
    }

    public function uninstall()
    {
        $query = "
            ALTER 
            TABLE `" . Common::prefixTable('log_visit') . "` 
                DROP `location_hostname`
        ";
        Db::exec($query);
    }

    /**
     *
     * @param array $visitorInfo            
     */
    public function logIp2Hostname(array &$visitorInfo)
    {
        $ip = $visitorInfo['location_ip'];
        $ip = IP::N2P($ip);
        
        $hostname = gethostbyaddr($ip);
        if ($hostname !== false && $hostname !== $ip) {
            $visitorInfo['location_hostname'] = $hostname;
        }
    }
}
