<?php

namespace AndreySerdjuk\SoapClientExtended;

use Gaufrette\Adapter\InMemory as InMemoryAdapter;
use Gaufrette\Filesystem;
use Gaufrette\StreamWrapper;

class SoapClient extends \SoapClient
{
    /**
     * @var array|null
     */
    protected $curlOptions = array();

    public function __construct($wsdl, array $options = null)
    {
        if (!empty($options['curl_options']) && is_array($options['curl_options'])) {
            $this->curlOptions = $options['curl_options'];
        }

        $wsdl = static::doRequest($this->curlOptions, $wsdl);
        $wsdlKey = md5($wsdl);

        $adapter = new InMemoryAdapter(array($wsdlKey => $wsdl));
        $filesystem = new Filesystem($adapter);

        $map = StreamWrapper::getFilesystemMap();
        $map->set('foo', $filesystem);

        StreamWrapper::register('wsdlfile');

        parent::__construct('wsdlfile://foo/' . $wsdlKey, $options);

        stream_wrapper_unregister('wsdlfile');
    }
    
    public function __doRequest($request, $location, $action = null, $version, $one_way = 0)
    {
        return static::doRequest(
            array(
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: "' . $action . '"',
                    'User-Agent: PHP-SOAP/5.6.24',
                ),
                CURLOPT_POSTFIELDS => $request,
            ) + $this->curlOptions,
            $location
        );
    }

    protected static function doRequest($curlOpts, $location, $action = null, $version = null, $one_way = 0)
    {
        $handle = curl_init();

        curl_setopt_array($handle, $curlOpts);

        curl_setopt($handle, CURLOPT_URL, $location);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_FRESH_CONNECT, true);

        $response = curl_exec($handle);
        curl_close($handle);

        return $response;
    }
}
