<?php
declare(strict_types=1);
namespace Bss\ResolveDuplicateProductUrl\Observer;

use Bss\ResolveDuplicateProductUrl\Model\ResourceModel\EavAttribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\Escaper;

/**
 * Class DuplicateUrlKeyObserver
 * Escape rewrite url error by modify the url_key to new unique key follow format url_key-{number}
 */
class DuplicateUrlKeyObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var EavAttribute
     */
    protected $eavAttributeResource;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * DuplicateUrlKeyObserver constructor.
     *
     * @param EavAttribute $eavAttributeResource
     * @param ManagerInterface $messageManager
     * @param Escaper $escaper
     */
    public function __construct(
        EavAttribute $eavAttributeResource,
        ManagerInterface $messageManager,
        Escaper $escaper
    ) {
        $this->eavAttributeResource = $eavAttributeResource;
        $this->messageManager = $messageManager;
        $this->escaper = $escaper;
    }

    /**
     * Check existence of product url_key and make it unique
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getProduct();

        if (!$product || !$product->getUrlKey()) {
            return;
        }

        $urlKey = $product->getUrlKey();
        $uniqueUrl = $urlKey;
        $index = 2;
        $isExisted = true;

        while ($isExisted) {
            $isExisted = $this->eavAttributeResource->isProductUrlKeyExists($uniqueUrl);
            if ($isExisted) {
                $uniqueUrl = sprintf("%s-%s", $urlKey, $index);
                $index++;
            }
        }

        if ($uniqueUrl !== $urlKey) {
            $product->setUrlKey($uniqueUrl);
            $this->messageManager->addNoticeMessage(
                __(
                    'URL Key for product %1 has been changed to %2.',
                    $this->escaper->escapeHtml($product->getName()),
                    $this->escaper->escapeHtml($uniqueUrl)
                )
            );
        }
    }
}
