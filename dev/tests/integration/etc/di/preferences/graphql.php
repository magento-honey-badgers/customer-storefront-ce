<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\StorefrontTestFixer\CustomerAfterSaveAndAfterDelete;

return [
    \Magento\CustomerStorefrontSynchronizer\Model\ResourceModel\Plugin\CustomerStorefrontPublisherPlugin::class => CustomerAfterSaveAndAfterDelete::class

];
