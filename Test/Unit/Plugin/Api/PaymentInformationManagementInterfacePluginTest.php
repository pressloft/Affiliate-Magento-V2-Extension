<?php

namespace PressLoft\Affiliate\Test\Unit\Plugin\Api;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PressLoft\Affiliate\Helper\Config;
use PressLoft\Affiliate\Plugin\Api\PaymentInformationManagementInterfacePlugin;
use PressLoft\Affiliate\Service\CreateAffiliateService;
use Magento\Framework\Stdlib\CookieManagerInterface;

class PaymentInformationManagementInterfacePluginTest extends TestCase
{
    /**
     * @var PaymentInformationManagementInterface|MockObject
     */
    private $subjectMock;

    /**
     * @var PaymentInformationManagementInterfacePlugin
     */
    private $object;

    /**
     * @var CreateAffiliateService|MockObject
     */
    private $createAffiliateService;

    /**
     * @var Config|MockObject
     */
    private $helper;

    /**
     * @var CookieManagerInterface|MockObject
     */
    private $cookieManagerInterface;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    private $cookieMetadataFactory;

    /**
     * @var CookieMetadata|MockObject
     */
    private $cookieMetadata;

    /**
     * Setup tests
     */
    protected function setUp(): void
    {
        $this->createAffiliateService = $this->getMockBuilder(CreateAffiliateService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieManagerInterface = $this->getMockForAbstractClass(
            CookieManagerInterface::class
        );

        $this->cookieMetadataFactory = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subjectMock = $this->getMockForAbstractClass(PaymentInformationManagementInterface::class);

        $this->cookieMetadata = $this->createMock(CookieMetadata::class);

        $this->object = new PaymentInformationManagementInterfacePlugin(
            $this->createAffiliateService,
            $this->helper,
            $this->cookieManagerInterface,
            $this->cookieMetadataFactory
        );
    }

    /**
     * @return void
     */
    public function testAfterSavePaymentInformationAndPlaceOrderWithoutEnable(): void
    {
        $orderId = '00000455';

        $this->helper->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->cookieManagerInterface->expects($this->never())
            ->method('getCookie');

        $this->createAffiliateService->expects($this->never())
            ->method('save');

        $this->cookieMetadataFactory->expects($this->never())
            ->method('createPublicCookieMetadata');

        $this->cookieMetadata->expects($this->never())
            ->method('setPath');

        $this->cookieManagerInterface->expects($this->never())
            ->method('deleteCookie');

        $this->assertSame($orderId, $this->object->afterSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $orderId
        ));
    }

    /**
     * @return void
     */
    public function testAfterSavePaymentInformationAndPlaceOrder(): void
    {
        $orderId = '00000455';
        $token = 'token';

        $this->helper->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        $this->cookieManagerInterface->expects($this->any())
            ->method('getCookie')
            ->with(\PressLoft\Affiliate\Model\Affiliate::TOKEN)
            ->willReturn($token);

        $this->createAffiliateService->expects($this->any())
            ->method('save')
            ->with($orderId, $token);

        $this->cookieMetadataFactory->expects($this->any())
            ->method('createPublicCookieMetadata')
            ->willReturn($this->cookieMetadata);

        $this->cookieMetadata->expects($this->once())
            ->method('setPath')
            ->with('/');

        $this->cookieManagerInterface->expects($this->any())
            ->method('deleteCookie')
            ->with(\PressLoft\Affiliate\Model\Affiliate::TOKEN);

        $this->assertSame($orderId, $this->object->afterSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $orderId
        ));
    }
}
