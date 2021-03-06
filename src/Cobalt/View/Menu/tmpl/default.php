<?php
/*------------------------------------------------------------------------
# Cobalt
# ------------------------------------------------------------------------
# @author Cobalt
# @copyright Copyright (C) 2012 cobaltcrm.org All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website: http://www.cobaltcrm.org
-------------------------------------------------------------------------*/
// no direct access
defined( '_CEXEC' ) or die( 'Restricted access' ); ?>

<div class="container-fluid">
    <?php echo $this->side_menu['quick_menu']->render(); ?>
    <div class="row">
        <div class="col-sm-12" id="content">
            <div id="system-message-container"></div>
            <div class="row">
                <?php echo $this->side_menu['menu']->render(); ?>
                <div class="col-md-9">
                    <form action="index.php?view=menu" method="post" name="adminForm" id="adminForm" class="form-validate"  >
                        <legend><h3 id="header"><?php echo JText::_('COBALT_EDIT_MENU'); ?></h3></legend>
                        <div class="alert alert-info"><?php echo JText::_('COBALT_EDIT_MENU_DESC'); ?></div>
                        <?php foreach ($this->menu_template as $menu_item) { ?>
                            <div class="form-group">
                            <label class="checkbox">
                                <input type="checkbox" class="form-control" name="menu_items[]" value="<?php echo $menu_item; ?>" <?php if (is_array($this->menu->menu_items) && in_array($menu_item,$this->menu->menu_items)) { echo 'checked="checked"'; } ?> />
                                <?php echo JText::_('COBALT_'.strtoupper($menu_item)); ?>
                            </label>
                            </div>
                        <?php } ?>
                        <div>
                            <input type="hidden" name="id" value="1" />
                            <input type="hidden" name="task" value="" />
                            <input type="hidden" name="controller" value="" />
                            <input type="hidden" name="model" value="menu" />
                            <?php echo JHtml::_('form.token'); ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
