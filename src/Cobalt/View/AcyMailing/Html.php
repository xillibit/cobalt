<?php
/*------------------------------------------------------------------------
# Cobalt
# ------------------------------------------------------------------------
# @author Cobalt
# @copyright Copyright (C) 2012 cobaltcrm.org All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website: http://www.cobaltcrm.org
-------------------------------------------------------------------------*/

namespace Cobalt\View\AcyMailing;

use Cobalt\Helper\MailinglistsHelper;

defined( '_CEXEC' ) or die( 'Restricted access' );

use Joomla\View\AbstractHtmlView;

class Html extends AbstractHtmlView
{
    public function render($tpl = null)
    {
        $this->mailing_lists = MalinglistsHelper::getMailingLists();
        $this->newsletters = MalinglistsHelper::getNewsletters();

        //display
        return parent::render();
    }

}
