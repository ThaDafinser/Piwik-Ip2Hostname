<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ip2Hostname\Columns;

use Piwik\Common;
use Piwik\Network\IP;
use Piwik\Network\IPUtils;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\Provider\Provider as ProviderPlugin;

class Ip2Hostname extends VisitDimension
{

    protected $columnName = 'location_hostname';

    protected $columnType = 'VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL';

    public function getName()
    {
        return Piwik::translate('Ip2Hostname_LocationHostname');
    }

    public function getRequiredVisitFields()
    {
        return array(
            'location_ip'
        );
    }

    /**
     *
     * @param Request $request            
     * @param Visitor $visitor            
     * @param Action|null $action            
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $ip = $visitor->getVisitorColumn('location_ip');
        
        $privacyConfig = new PrivacyManagerConfig();
        if (! $privacyConfig->useAnonymizedIpForVisitEnrichment) {
            $ip = $request->getIp();
        }
        
        $ip = IPUtils::binaryToStringIP($ip);
        
        // In case the IP was anonymized, we should not continue since the DNS reverse lookup will fail and this will slow down tracking
        if (substr($ip, - 2, 2) == '.0') {
            Common::printDebug("IP Was anonymized so we skip the DNS reverse lookup...");
            
            return null;
        }
        
        $ip = IP::fromStringIP($ipStr);
        
        return $ip->getHostname();
    }
}
