<?php

namespace PressLoft\Affiliate\Test\Unit\Controller\Token;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use PHPUnit\Framework\TestCase;
use PressLoft\Affiliate\Controller\Token\Validate;
use PressLoft\Affiliate\Model\Affiliate;
use PressLoft\Affiliate\Service\CookiePeriodCheck;
use Psr\Log\LoggerInterface;

class ValidateTest extends TestCase
{
    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var JsonFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultJsonFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CookiePeriodCheck
     */
    protected $cookiePeriodCheck;

    /**
     * @var CookieManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cookieMetadataFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    protected $logger;

    /**
     * @var Validate
     */
    protected $object;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(RequestInterface::class)->getMockForAbstractClass();

        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookiePeriodCheck = $this->getMockBuilder(CookiePeriodCheck::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieManager = $this->getMockBuilder(CookieManagerInterface::class)
            ->getMockForAbstractClass();

        $this->cookieMetadataFactory = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->cookieMetadata = $this->createMock(CookieMetadata::class);

        $this->object = new Validate(
            $this->request,
            $this->resultJsonFactory,
            $this->cookiePeriodCheck,
            $this->cookieManager,
            $this->cookieMetadataFactory,
            $this->logger
        );
    }

    public function testExecute()
    {
        $token = 'token';

        $result = [
            'success' => true,
            'message' => __('Token is valid.')
        ];

        $this->request->expects($this->any())
            ->method('getParam')
            ->with(Affiliate::TOKEN)
            ->willReturn($token);

        $param = $this->cookiePeriodCheck->expects($this->any())
            ->method('execute')
            ->with($token);

        $this->cookieMetadataFactory->expects($this->any())
            ->method('createPublicCookieMetadata')
            ->willReturn($this->cookieMetadata);

        $this->cookieMetadata->expects($this->any())
            ->method('setPath')
            ->with('/');

        $this->cookieManager->expects($this->any())
            ->method('setPublicCookie')
            ->with(Affiliate::TOKEN, $token, $this->cookieMetadata);

        $this->assertNotNull($this->cookieManager);
    }
}
