<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload8bf3ab826e035b961472c5593aa1b47b($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'openidconnectclientplugin' => '/openidconnectclientPlugin.class.php',
            'openidconnectclientplugindescriptor' => '/OpenIDConnectClientPluginDescriptor.class.php',
            'openidconnectclientplugininfo' => '/OpenIDConnectClientPluginInfo.class.php',
            'tuleap\\openidconnectclient\\accountlinker\\controller' => '/OpenIDConnectClient/AccountLinker/Controller.php',
            'tuleap\\openidconnectclient\\accountlinker\\presenter' => '/OpenIDConnectClient/AccountLinker/Presenter.php',
            'tuleap\\openidconnectclient\\accountlinker\\unlinkedaccount' => '/OpenIDConnectClient/AccountLinker/UnlinkedAccount.php',
            'tuleap\\openidconnectclient\\accountlinker\\unlinkedaccountdao' => '/OpenIDConnectClient/AccountLinker/UnlinkedAccountDao.php',
            'tuleap\\openidconnectclient\\accountlinker\\unlinkedaccountdataaccessexception' => '/OpenIDConnectClient/AccountLinker/UnlinkedAccountDataAccessException.php',
            'tuleap\\openidconnectclient\\accountlinker\\unlinkedaccountmanager' => '/OpenIDConnectClient/AccountLinker/UnlinkedAccountManager.php',
            'tuleap\\openidconnectclient\\accountlinker\\unlinkedaccountnotfoundexception' => '/OpenIDConnectClient/AccountLinker/UnlinkedAccountNotFoundException.php',
            'tuleap\\openidconnectclient\\authentication\\authorizationdispatcher' => '/OpenIDConnectClient/Authentication/AuthorizationDispatcher.php',
            'tuleap\\openidconnectclient\\authentication\\flow' => '/OpenIDConnectClient/Authentication/Flow.php',
            'tuleap\\openidconnectclient\\authentication\\flowresponse' => '/OpenIDConnectClient/Authentication/FlowResponse.php',
            'tuleap\\openidconnectclient\\authentication\\sessionstate' => '/OpenIDConnectClient/Authentication/SessionState.php',
            'tuleap\\openidconnectclient\\authentication\\state' => '/OpenIDConnectClient/Authentication/State.php',
            'tuleap\\openidconnectclient\\authentication\\statefactory' => '/OpenIDConnectClient/Authentication/StateFactory.php',
            'tuleap\\openidconnectclient\\authentication\\statemanager' => '/OpenIDConnectClient/Authentication/StateManager.php',
            'tuleap\\openidconnectclient\\authentication\\statestorage' => '/OpenIDConnectClient/Authentication/StateStorage.php',
            'tuleap\\openidconnectclient\\login\\connectorpresenter' => '/OpenIDConnectClient/Login/ConnectorPresenter.php',
            'tuleap\\openidconnectclient\\login\\connectorpresenterbuilder' => '/OpenIDConnectClient/Login/ConnectorPresenterBuilder.php',
            'tuleap\\openidconnectclient\\login\\controller' => '/OpenIDConnectClient/Login/Controller.php',
            'tuleap\\openidconnectclient\\provider\\provider' => '/OpenIDConnectClient/Provider/Provider.php',
            'tuleap\\openidconnectclient\\provider\\providerdao' => '/OpenIDConnectClient/Provider/ProviderDao.php',
            'tuleap\\openidconnectclient\\provider\\providermanager' => '/OpenIDConnectClient/Provider/ProviderManager.php',
            'tuleap\\openidconnectclient\\provider\\providernotfoundexception' => '/OpenIDConnectClient/Provider/ProviderNotFoundException.php',
            'tuleap\\openidconnectclient\\router' => '/OpenIDConnectClient/Router.php',
            'tuleap\\openidconnectclient\\usermapping\\controller' => '/OpenIDConnectClient/UserMapping/Controller.php',
            'tuleap\\openidconnectclient\\usermapping\\usermapping' => '/OpenIDConnectClient/UserMapping/UserMapping.php',
            'tuleap\\openidconnectclient\\usermapping\\usermappingdao' => '/OpenIDConnectClient/UserMapping/UserMappingDao.class.php',
            'tuleap\\openidconnectclient\\usermapping\\usermappingdataaccessexception' => '/OpenIDConnectClient/UserMapping/UserMappingDataAccessException.php',
            'tuleap\\openidconnectclient\\usermapping\\usermappingmanager' => '/OpenIDConnectClient/UserMapping/UserMappingManager.php',
            'tuleap\\openidconnectclient\\usermapping\\usermappingnotfoundexception' => '/OpenIDConnectClient/UserMapping/UserMappingNotFoundException.php',
            'tuleap\\openidconnectclient\\usermapping\\usermappingusage' => '/OpenIDConnectClient/UserMapping/UserMappingUsage.php',
            'tuleap\\openidconnectclient\\usermapping\\userpreferencespresenter' => '/OpenIDConnectClient/UserMapping/UserPreferencesPresenter.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload8bf3ab826e035b961472c5593aa1b47b');
// @codeCoverageIgnoreEnd
