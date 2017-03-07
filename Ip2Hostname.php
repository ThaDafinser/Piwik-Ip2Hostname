<?php
namespace Piwik\Plugins\Ip2Hostname;

use Piwik\Plugin;

class Ip2Hostname extends Plugin
{

    /**
     *
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return [
            'Live.getAllVisitorDetails' => 'getAllVisitorDetails',
        ];
    }

    public function getAllVisitorDetails(&$visitor, $details)
    {
        if (isset($visitor['visitIp']) && isset($details['location_hostname']) && $details['location_hostname'] != '') {
            $visitor['visitIp'] = $visitor['visitIp'] . ' | ' . $details['location_hostname'];
        } elseif (isset($details['location_hostname']) && $details['location_hostname'] != '') {
            $visitor['visitIp'] = $details['location_hostname'];
        }
    }
}
