<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Framework\Api\SearchCriteria;

class QuoteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuoteRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteCollectionMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->quoteFactoryMock = $this->getMock('\Magento\Quote\Model\QuoteFactory', ['create'], [], '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Framework\Store\StoreManagerInterface');
        $this->quoteMock = $this->getMock(
            '\Magento\Quote\Model\Quote',
            ['load', 'loadByCustomer', 'getIsActive', 'getId', '__wakeup', 'setSharedStoreIds', 'save', 'delete',
                'getCustomerId'],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->searchResultsBuilderMock = $this->getMock(
            '\Magento\Quote\Api\Data\CartSearchResultsDataBuilder',
            ['setSearchCriteria', 'setTotalCount', 'setItems', 'create'],
            [],
            '',
            false
        );

        $this->quoteCollectionMock = $this->getMock('Magento\Quote\Model\Resource\Quote\Collection', [], [], '', false);

        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\QuoteRepository',
            [
                'quoteFactory' => $this->quoteFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'searchResultsBuilder' => $this->searchResultsBuilderMock,
                'quoteCollection' => $this->quoteCollectionMock,
            ]
        );
    }

    public function testCreate()
    {
        $this->quoteFactoryMock->expects($this->once())
            ->method('create')
            ->with([1, 2, 3])
            ->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeMock->expects($this->never())->method('getId');
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->never())->method('load');
        $this->quoteMock->expects($this->never())->method('getId');

        $this->assertEquals($this->quoteMock, $this->model->create([1, 2, 3]));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 14
     */
    public function testGetWithExceptionById()
    {
        $cartId = 14;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(false);

        $this->model->get($cartId);
    }

    public function testGet()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);

        $this->assertEquals($this->quoteMock, $this->model->get($cartId));
        $this->assertEquals($this->quoteMock, $this->model->get($cartId));
    }

    public function testGetWithSharedStoreIds()
    {
        $cartId = 16;
        $sharedStoreIds = [1, 2];

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())
            ->method('setSharedStoreIds')
            ->with($sharedStoreIds)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);

        $this->assertEquals($this->quoteMock, $this->model->get($cartId, $sharedStoreIds));
        $this->assertEquals($this->quoteMock, $this->model->get($cartId, $sharedStoreIds));
    }

    public function testGetForCustomer()
    {
        $cartId = 17;
        $customerId = 23;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('loadByCustomer')
            ->with($customerId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->exactly(2))->method('getId')->willReturn($cartId);

        $this->assertEquals($this->quoteMock, $this->model->getForCustomer($customerId));
        $this->assertEquals($this->quoteMock, $this->model->getForCustomer($customerId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 14
     */
    public function testGetActiveWithExceptionById()
    {
        $cartId = 14;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(false);
        $this->quoteMock->expects($this->never())->method('getIsActive');

        $this->model->getActive($cartId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with cartId = 15
     */
    public function testGetActiveWithExceptionByIsActive()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->once())->method('getIsActive')->willReturn(0);

        $this->model->getActive($cartId);
    }

    public function testGetActive()
    {
        $cartId = 15;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->exactly(2))->method('getIsActive')->willReturn(1);

        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId));
        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId));
    }

    public function testGetActiveWithSharedStoreIds()
    {
        $cartId = 16;
        $sharedStoreIds = [1, 2];

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())
            ->method('setSharedStoreIds')
            ->with($sharedStoreIds)
            ->willReturnSelf();
        $this->quoteMock->expects($this->once())
            ->method('load')
            ->with($cartId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->exactly(2))->method('getIsActive')->willReturn(1);

        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId, $sharedStoreIds));
        $this->assertEquals($this->quoteMock, $this->model->getActive($cartId, $sharedStoreIds));
    }

    public function testGetActiveForCustomer()
    {
        $cartId = 17;
        $customerId = 23;

        $this->quoteFactoryMock->expects($this->once())->method('create')->willReturn($this->quoteMock);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())->method('getId')->willReturn($this->storeMock);
        $this->quoteMock->expects($this->never())->method('setSharedStoreIds');
        $this->quoteMock->expects($this->once())
            ->method('loadByCustomer')
            ->with($customerId)
            ->willReturn($this->storeMock);
        $this->quoteMock->expects($this->exactly(2))->method('getId')->willReturn($cartId);
        $this->quoteMock->expects($this->exactly(2))->method('getIsActive')->willReturn(1);

        $this->assertEquals($this->quoteMock, $this->model->getActiveForCustomer($customerId));
        $this->assertEquals($this->quoteMock, $this->model->getActiveForCustomer($customerId));
    }

    public function testSave()
    {
        $this->quoteMock->expects($this->once())
            ->method('save');
        $this->quoteMock->expects($this->exactly(1))->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->exactly(1))->method('getCustomerId')->willReturn(2);

        $this->model->save($this->quoteMock);
    }

    public function testDelete()
    {
        $this->quoteMock->expects($this->once())
            ->method('delete');
        $this->quoteMock->expects($this->exactly(1))->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->exactly(1))->method('getCustomerId')->willReturn(2);

        $this->model->delete($this->quoteMock);
    }

    /**
     * @param int $direction
     * @param string $expectedDirection
     * @dataProvider getListSuccessDataProvider
     */
    public function testGetListSuccess($direction, $expectedDirection)
    {
        $searchResult = $this->getMock('\Magento\Quote\Api\Data\CartSearchResultsInterface', [], [], '', false);
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $cartMock = $this->getMock('Magento\Payment\Model\Cart', [], [], '', false);
        $filterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $pageSize = 10;

        $this->searchResultsBuilderMock
            ->expects($this->once())
            ->method('setSearchCriteria');

        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $searchCriteriaMock
            ->expects($this->any())
            ->method('getFilterGroups')
            ->will($this->returnValue([$filterGroupMock]));

        //addFilterGroupToCollection() checks
        $filterGroupMock->expects($this->any())->method('getFilters')->will($this->returnValue([$filterMock]));
        $filterMock->expects($this->once())->method('getField')->will($this->returnValue('store_id'));
        $filterMock->expects($this->any())->method('getConditionType')->will($this->returnValue('eq'));
        $filterMock->expects($this->once())->method('getValue')->will($this->returnValue('filter_value'));

        //back in getList()
        $this->quoteCollectionMock->expects($this->once())->method('getSize')->willReturn($pageSize);
        $this->searchResultsBuilderMock->expects($this->once())->method('setTotalCount')->with($pageSize);
        $sortOrderMock = $this->getMockBuilder('Magento\Framework\Api\SortOrder')
            ->setMethods(['getField', 'getDirection'])
            ->disableOriginalConstructor()
            ->getMock();

        //foreach cycle
        $searchCriteriaMock
            ->expects($this->once())
            ->method('getSortOrders')
            ->will($this->returnValue([$sortOrderMock]));
        $sortOrderMock->expects($this->once())->method('getField')->will($this->returnValue('id'));
        $sortOrderMock->expects($this->once())->method('getDirection')->will($this->returnValue($direction));
        $this->quoteCollectionMock->expects($this->once())->method('addOrder')->with('id', $expectedDirection);


        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->will($this->returnValue(1));
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->will($this->returnValue(10));
        $this->quoteCollectionMock->expects($this->once())->method('setCurPage')->with(1);
        $this->quoteCollectionMock->expects($this->once())->method('setPageSize')->with(10);


        $this->quoteCollectionMock->expects($this->once())->method('getItems')->willReturn([$cartMock]);
        $this->searchResultsBuilderMock->expects($this->once())->method('setItems')->with([$cartMock]);
        $this->searchResultsBuilderMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchResult));
        $this->assertEquals($searchResult, $this->model->getList($searchCriteriaMock));
    }

    /**
     * @return array
     */
    public function getListSuccessDataProvider()
    {
        return [
            'asc' => [SearchCriteria::SORT_ASC, 'ASC'],
            'desc' => [SearchCriteria::SORT_DESC, 'DESC']
        ];
    }
}
