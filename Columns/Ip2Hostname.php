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
use Piwik\IP;
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


    public function getName()
    {
        return Piwik::translate('Provider_ColumnProvider');
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
        
        $ip = IP::N2P($ip);
        
        // In case the IP was anonymized, we should not continue since the DNS reverse lookup will fail and this will slow down tracking
        if (substr($ip, - 2, 2) == '.0') {
            Common::printDebug("IP Was anonymized so we skip the DNS reverse lookup...");
            return false;
        }
        
        $hostname = $this->getHost($ip);
        $hostnameExtension = ProviderPlugin::getCleanHostname($hostname);
        
        // add the provider value in the table log_visit
        $locationProvider = substr($hostnameExtension, 0, 100);
        
        return $locationProvider;
    }


    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('provider');
        $segment->setCategory('Visit Location');
        $segment->setName('Provider_ColumnProvider');
        $segment->setAcceptedValues('comcast.net, proxad.net, etc.');
        $this->addSegment($segment);
    }
    

    /**
     * Returns the hostname given the IP address string
     *
     * @param string $ip
     *            IP Address
     * @return string hostname (or human-readable IP address)
     */
    private function getHost($ip)
    {
        return trim(strtolower(@IP::getHostByAddr($ip)));
    }

}
