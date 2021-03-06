<?php
/*------------------------------------------------------------------------
# Cobalt
# ------------------------------------------------------------------------
# @author Cobalt
# @copyright Copyright (C) 2012 cobaltcrm.org All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website: http://www.cobaltcrm.org
-------------------------------------------------------------------------*/
// No direct access
defined( '_CEXEC' ) or die( 'Restricted access' );

/** @type \Cobalt\Application $this */

use Cobalt\Helper\UsersHelper;
use Cobalt\Helper\ActivityHelper;
use Cobalt\Helper\DateHelper;
use Cobalt\Helper\TemplateHelper;
use Cobalt\Helper\StylesHelper;

// Load Language
UsersHelper::loadLanguage();

//set site timezone
$tz = DateHelper::getSiteTimezone();

//Load plugins
JPluginHelper::importPlugin('cobalt');

//get user object
$user = $this->getUser();

// Fetch the controller
$controllerObj = $this->getRouter()->getController($this->get('uri.route'));

// Require specific controller if requested
$controller = $this->input->get('controller', 'default');

//load user toolbar
$format = $this->input->get('format');

$overrides = array('ajax', 'mail', 'login');

$loggedIn = ($user->guest === 0 && $user->id);

if ($loggedIn && $format !== 'raw' && !in_array($controller, $overrides)) {

    ActivityHelper::saveUserLoginHistory();

    // Set a default view if none exists
    if (! $this->input->get('view')) {
        $this->input->set('view', 'dashboard' );
    }

    //Grab document instance
    $document = $this->getDocument();

    //load scripts
    $document->addScript( JURI::base().'libraries/crm/media/js/jquery.js' );
    $document->addScript( JURI::base().'libraries/crm/media/js/jquery-ui.js' );
    $document->addScript( JURI::base().'libraries/crm/media/js/jquery.tools.min.js' );
    $document->addScript( JURI::base().'libraries/crm/media/js/bootstrap.min.js' );
    $document->addScript( JURI::base().'libraries/crm/media/js/bootstrap-colorpicker.js' );
    $document->addScript( JURI::base().'libraries/crm/media/js/bootstrap-datepicker.js' );
    $document->addScript( JURI::base().'libraries/crm/media/js/bootstrap-fileupload.js' );

    //start component div wrapper
    if ( $this->input->get('view') != "print") {
        TemplateHelper::loadToolbar();
    }
    TemplateHelper::startCompWrap();

    //mobile detection
    if (TemplateHelper::isMobile()) {
         $this->input->set('tmpl','component');
         $document->addScript('http://maps.google.com/maps/api/js?sensor=false');
         $document->addScript( JURI::base().'libraries/crm/media/js/jquery.mobile.1.0.1.min.js' );
         $document->addScript( JURI::base().'libraries/crm/media/js/jquery.mobile.datepicker.js' );
         $document->addScript( JURI::base().'libraries/crm/media/js/jquery.mobile.map.js' );
         $document->addScript( JURI::base().'libraries/crm/media/js/jquery.mobile.map.extensions.js' );
         $document->addScript( JURI::base().'libraries/crm/media/js/jquery.mobile.map.services.js' );
         $document->addScript( JURI::base().'libraries/crm/media/js/cobalt.mobile.js');
         $document->setMetaData('viewport','width=device-width, initial-scale=1');
    } else {
        //load task events javascript which will be used throughout page redirects
        $document->addScript( JURI::base().'libraries/crm/media/js/timepicker.js');
        $document->addScript( JURI::base().'libraries/crm/media/js/cobalt.js' );
        $document->addScript( JURI::base().'libraries/crm/media/js/filters.js');
        $document->addScript( JURI::base().'libraries/crm/media/js/autogrow.js');
        $document->addScript( JURI::base().'libraries/crm/media/js/jquery.cluetip.min.js');

    }

    //load styles
    StylesHelper::loadStyleSheets();

    //load javascript language
    TemplateHelper::loadJavascriptLanguage();
}

if (!$loggedIn && !($controllerObj instanceof Cobalt\Controller\Login)) {
    $this->redirect(RouteHelper::_('index.php?view=login'));
}

//fullscreen detection
if (UsersHelper::isFullscreen()) {
    $this->input->set('tmpl', 'component' );
}

// Perform the Request task
$controllerObj->execute();

//end componenet wrapper
if ($user !== false && $format !== 'raw') {
    TemplateHelper::endCompWrap();
}
