<?php

$fileContent = 'class JobboardEuraxessImporter {
    
    private $soapClient;
    
    public function __construct() {
        DebugLogger::debug(\'JobboardEuraxessImporter#__construct\');
        try {
            ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
            $this->soapClient = new \SoapClient( 
                \'http://ec.europa.eu/euraxess/ws/JV.cfc?wsdl\',
                array( 
                    "location" => "http://ec.europa.eu/euraxess/ws/JV.cfc",
                    "soap_version" => SOAP_1_1,
                    "trace" => 1
                ) 
            );
        } catch (\Exception $e) {
            Logger::getInstance()->addException($e);
            throw new GeneralException(\'Unable to initialize importer.\', $e->getMessage());
        }
    }';
    

    $fileContent = preg_replace("/(\/\*.*\*\/)/sU", "", $fileContent);
    $fileContent = preg_replace("/(\'.*\')/sU", "", $fileContent);
    $fileContent = preg_replace("/(\".*\")/sU", "", $fileContent);
    $fileContent = preg_replace("/(\/\/.*)/", "", $fileContent);
        
    echo $fileContent;