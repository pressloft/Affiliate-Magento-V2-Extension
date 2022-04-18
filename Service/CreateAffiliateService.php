<?php

namespace PressLoft\Affiliate\Service;

use Magento\Framework\Exception\AlreadyExistsException;
use PressLoft\Affiliate\Model\AffiliateFactory;
use PressLoft\Affiliate\Model\AffiliateSchedule;
use PressLoft\Affiliate\Model\AffiliateScheduleFactory;
use PressLoft\Affiliate\Model\ResourceModel\Affiliate as AffiliateResourceModel;
use PressLoft\Affiliate\Model\ResourceModel\AffiliateSchedule as AffiliateScheduleResourceModel;

/**
 * Command service to create Affiliate records based on Order ID and Token
 */
class CreateAffiliateService
{
    /**
     * @var AffiliateFactory
     */
    protected $affiliateModelFactory;

    /**
     * @var AffiliateScheduleFactory
     */
    protected $affiliateScheduleModelFactory;

    /**
     * @var AffiliateResourceModel
     */
    protected $resourceAffiliate;

    /**
     * @var AffiliateScheduleResourceModel
     */
    protected $resourceAffiliateSchedule;

    /**
     * @param AffiliateFactory $affiliateModelFactory
     * @param AffiliateScheduleFactory $affiliateScheduleModelFactory
     * @param AffiliateResourceModel $resourceAffiliate
     * @param AffiliateScheduleResourceModel $resourceAffiliateSchedule
     */
    public function __construct(
        AffiliateFactory $affiliateModelFactory,
        AffiliateScheduleFactory $affiliateScheduleModelFactory,
        AffiliateResourceModel $resourceAffiliate,
        AffiliateScheduleResourceModel $resourceAffiliateSchedule
    ) {
        $this->affiliateModelFactory = $affiliateModelFactory;
        $this->affiliateScheduleModelFactory = $affiliateScheduleModelFactory;
        $this->resourceAffiliate = $resourceAffiliate;
        $this->resourceAffiliateSchedule = $resourceAffiliateSchedule;
    }

    /**
     * Saving data to table
     *
     * @param int|string $orderId
     * @param string $token
     * @return void
     * @throws AlreadyExistsException
     */
    public function save($orderId, string $token)
    {
        $affiliate = $this->affiliateModelFactory->create()
            ->setOrderId($orderId)
            ->setToken($token);
        $this->resourceAffiliate
            ->save($affiliate);

        $affiliateSchedule = $this->affiliateScheduleModelFactory->create()
            ->setAffiliateId($affiliate->getId())
            ->setStatus(AffiliateSchedule::STATUS_NEW);
        $this->resourceAffiliateSchedule
            ->save($affiliateSchedule);
    }
}
