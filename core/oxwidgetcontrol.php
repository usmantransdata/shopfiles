<?php

/**
 * Main shop actions controller. Processes user actions, logs
 * them (if needed), controls output, redirects according to
 * processed methods logic. This class is initialized from index.php
 */
class oxWidgetControl extends oxShopControl
{
    /**
     * Skip handler set for widget as it already set in oxShopControl.
     *
     * @var bool
     */
    protected $_blHandlerSet = true;

    /**
     * Skip main tasks as it already handled in oxShopControl.
     *
     * @var bool
     */
    protected $_blMainTasksExecuted = true;

    /**
     * Create object and ensure that params have correct value.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Main shop widget manager. Sets needed parameters and calls parent::start method.
     *
     * Session variables:
     * <b>actshop</b>
     *
     * @param string $sClass      Class name
     * @param string $sFunction   Function name
     * @param array  $aParams     Parameters array
     * @param array  $aViewsChain Array of views names that should be initialized also
     *
     * @return null
     */
    public function start( $sClass = null, $sFunction = null, $aParams = null, $aViewsChain = null )
    {
        //$aParams = ( isset($aParams) ) ? $aParams : oxConfig::getParameter( 'oxwparams' );

        if ( !isset($aViewsChain) && oxConfig::getParameter( 'oxwparent' ) ) {
            $aViewsChain =  explode( "|", oxConfig::getParameter( 'oxwparent' ) );
        }

        parent::start( $sClass, $sFunction, $aParams, $aViewsChain );

        //perform tasks that should be done at the end of widget processing
        $this->_runLast();
    }

    /**
     * This function is only executed one time here we perform checks if we
     * only need once per session. There is no need to execute it if there
     * is views chain as parent view already executed it.
     *
     * @return null
     */
    protected function _runOnce()
    {

        return;
    }

    /**
     * Runs actions that should be performed at the controller finish.
     *
     * @return null
     */
    protected function _runLast()
    {
        $oConfig = $this->getConfig();

        if ( $oConfig->hasActiveViewsChain() ) {
            //removing current active view
            $oConfig->dropLastActiveView();

            // setting back last active view
            $oSmarty = oxRegistry::get("oxUtilsView")->getSmarty();
            $oSmarty->assign('oView', $oConfig->getActiveView() );
        }
    }

    /**
     * Initialize and return widget view object
     *
     * @param string $sClass      view name
     * @param string $sFunction   function name
     * @param array  $aParams     Parameters array
     * @param array  $aViewsChain Array of views names that should be initialized also
     *
     * @return oxView Current active view
     */
    protected function _initializeViewObject( $sClass, $sFunction, $aParams = null, $aViewsChain = null )
    {
        $oConfig = $this->getConfig();
        $aActiveViewsNames = $oConfig->getActiveViewsNames();
        $aActiveViewsNames = array_map( "strtolower", $aActiveViewsNames );

        // if exists views chain, initializing these view at first
        if ( is_array($aViewsChain) && !empty($aViewsChain) ) {

            foreach ( $aViewsChain as $sParentClassName ) {
                if ( $sParentClassName != $sClass && !in_array( strtolower($sParentClassName), $aActiveViewsNames ) ) {
                    // creating parent view object
                    if ( strtolower($sParentClassName) == 'oxubase' ) {
                        $oViewObject = oxNew( 'oxubase' );
                        $oConfig->setActiveView( $oViewObject );
                    } else {
                        $oViewObject = oxNew( $sParentClassName );
                        $oViewObject->setClassName( $sParentClassName );
                        $oConfig->setActiveView( $oViewObject );
                    }
                }
            }
        }

        $oWidgetViewObject = parent::_initializeViewObject( $sClass, $sFunction, $aParams );

        //set template name for current widget
        if ( $aParams['oxwtemplate'] ) {
            $oWidgetViewObject->setTemplateName( $aParams['oxwtemplate'] );
        }

        return $oWidgetViewObject;
    }
}
