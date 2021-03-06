<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Selection;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid',
                'adminhtml.catalog.product.edit.tab.bundle.option.search.grid'
            )->setIndex(
                $this->getRequest()->getParam('index')
            )->toHtml()
        );
    }
}
